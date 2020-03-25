<?php namespace Waka\Compilator\Classes;

class WordCreator2 extends WordProcessor2
{

    private $dataSourceModel;
    private $dataSourceId;
    private $additionalParams;
    private $dataSourceAdditionalParams;

    use \Waka\Cloudis\Classes\Traits\CloudisKey;

    public function prepareCreatorVars($dataSourceId)
    {
        $this->dataSourceModel = $this->linkModelSource($dataSourceId);
        $this->dotedValues = $this->getDotedValues();

    }
    public function setAdditionalParams($additionalParams)
    {
        if ($additionalParams) {
            $this->additionalParams = $additionalParams;
        }
    }
    private function linkModelSource($dataSourceId)
    {
        $this->dataSourceId = $dataSourceId;
        // si vide on puise dans le test
        if (!$this->dataSourceId) {
            $this->dataSourceId = $this->document->data_source->test_id;
        }
        //on enregistre le modèle
        return $this->document->data_source->modelClass::find($this->dataSourceId);
    }
    public function renderWord($dataSourceId)
    {
        $this->prepareCreatorVars($dataSourceId);
        $originalTags = $this->checkTags();

        //Traitement des champs simples
        foreach ($originalTags['injections'] as $injection) {
            $value = $this->dotedValues[$injection];
            $this->templateProcessor->setValue($injection, $value);
        }
        //trace_log("image Key ");
        //trace_log($originalTags['imagekeys']);
        foreach ($originalTags['imagekeys'] as $imagekey) {
            $tag = $this->getWordImageKey($imagekey);
            $key = $this->cleanWordKey($tag);
            $url = $this->decryptKeyedImage($key, $this->dataSourceModel);
            if ($url) {
                $this->templateProcessor->setImageValue($tag, $url);
            }
        }

        trace_log('traitement des fncs');
        //Préparation des resultat de toutes les fonbctions
        $data = $this->document->data_source->getFunctionsCollections($this->dataSourceId, $this->document);

        // Pour chazque fonctions dans le word
        foreach ($originalTags['fncs'] as $wordFnc) {
            // trace_log("-------------------------------");
            // trace_log($wordFnc);

            $functionName = $wordFnc['code'];
            // trace_log($functionName);

            $functionRows = $data[$functionName];
            // trace_log('-- functionRows --');
            // trace_log($functionRows);

            //Préparation du clone block
            $countFunctionRows = count($functionRows);
            $fncTag = 'fnc.' . $functionName;
            $this->templateProcessor->cloneBlock($fncTag, $countFunctionRows, true, true);
            $i = 1; //i permet de creer la cla #i lors du clone row

            //Parcours des lignes renvoyé par la fonctions
            foreach ($functionRows as $functionRow) {
                $functionRow = array_dot($functionRow);
                trace_log($functionRow);
                foreach ($wordFnc['subTags'] as $subTag) {
                    //trace_log('**subtag***');
                    //trace_log($subTag);
                    if (!$subTag['image']) {
                        $tag = $subTag['tag'] . '#' . $i;
                        //trace_log("c'est une value tag : " . $tag);
                        $value = $functionRow[$subTag['varName']];
                        $this->templateProcessor->setValue($tag, $value, 1);
                    } else {
                        $tag = $subTag['tag'] . '#' . $i;
                        //trace_log("c'est une image tag : " . $tag);
                        $path = $functionRow[$subTag['varName'] . '.path'];
                        $width = $functionRow[$subTag['varName'] . '.width'];
                        $height = $functionRow[$subTag['varName'] . '.height'];
                        $this->templateProcessor->setImageValue($tag, ['path' => $path, 'width' => $width . 'mm', 'height' => $height . 'mm'], 1);
                    }
                }
                $i++;

            }
        }
        $name = str_slug($this->document->name . '-' . $this->dataSourceModel->name);
        $coin = $this->templateProcessor->saveAs($name . '.docx');
        return response()->download($name . '.docx')->deleteFileAfterSend(true);
    }

    public function getDotedValues()
    {
        $array = [];
        // if ($this->additionalParams) {
        //     if (count($this->additionalParams)) {
        //         $rel = $this->document->data_source->getDotedRelationValues($this->dataSourceId, $this->additionalParams);
        //         //trace_log($rel);
        //         $array = array_merge($array, $rel);
        //         trace_log($array);
        //     }
        // }
        $rel = $this->document->data_source->getDotedValues($this->dataSourceId);
        //trace_log($rel);
        $array = array_merge($array, $rel);

        trace_log($array);
        return $array;
    }
}
