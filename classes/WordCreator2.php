<?php namespace Waka\Compilator\Classes;

class WordCreator2 extends WordProcessor2
{
    use \Waka\Utils\Classes\Traits\ConvertPx;

    private $dataSourceModel;
    private $dataSourceId;
    private $listImages;
    //private $additionalParams;
    //private $dataSourceAdditionalParams;

    use \Waka\Cloudis\Classes\Traits\CloudisKey;

    public function prepareCreatorVars($dataSourceId)
    {
        $this->dataSourceModel = $this->linkModelSource($dataSourceId);
        $this->dotedValues = $this->document->data_source->getDotedValues($dataSourceId);

        $getAllPicturesFromDataSource = [];
        $getAllPicturesFromDataSource['IMAGE'] = $this->document->data_source->getAllPictures($dataSourceId);
        $this->listImages = array_dot($getAllPicturesFromDataSource);

        //trace_log($this->listImages);
    }
    // public function setAdditionalParams($additionalParams)
    // {
    //     if ($additionalParams) {
    //         $this->additionalParams = $additionalParams;
    //     }
    // }
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
        foreach ($originalTags['IMAGE'] as $imagekey) {
            $url = $this->getUrlFromImageKey($imagekey);
            // $tag = $this->getWordImageKey($imagekey);
            // $key = $this->cleanWordKey($tag);
            // $url = $this->decryptKeyedImage($key, $this->dataSourceModel);
            if ($url) {
                $this->templateProcessor->setImageValue($imagekey, $url);
            }
        }

        //trace_log('traitement des fncs');
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
                //trace_log($functionRow);
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

    public function getUrlFromImageKey($imageKey)
    {
        $imageKey_array = explode(':', $imageKey);
        $idAndCrop = $imageKey_array[0];

        $idAndCrop_array = explode('**', $idAndCrop);
        $id = $idAndCrop_array[0];
        $crop = $idAndCrop_array[1] ?? 'fill';

        $nameOrId = $this->listImages[$id] ?? null;

        if (!$nameOrId) {
            return null;
        }

        $width = $imageKey_array[1] ?? '165mm';
        $height = $imageKey_array[2] ?? '165mm';

        $is_montage = is_numeric($nameOrId);

        $width = $this->convertStringToPx($width);
        $height = $this->convertStringToPx($height);

        //trace_log("width : " . $width);
        //trace_log("height : " . $height);

        $options = ['width' => $width, 'height' => $height, 'crop' => $crop];

        if ($is_montage) {
            return \Waka\Cloudis\Models\Montage::find($nameOrId)->getCloudiUrl($this->dataSourceId);
        } else {
            return \Cloudder::secureShow($nameOrId, $options);
        }

    }

    // public function getDotedValues()
    // {
    //     $array = [];
    //     // if ($this->additionalParams) {
    //     //     if (count($this->additionalParams)) {
    //     //         $rel = $this->document->data_source->getDotedRelationValues($this->dataSourceId, $this->additionalParams);
    //     //         //trace_log($rel);
    //     //         $array = array_merge($array, $rel);
    //     //         //trace_log($array);
    //     //     }
    //     // }
    //     $rel = $this->document->data_source->getDotedValues($this->dataSourceId);
    //     //trace_log($rel);
    //     $array = array_merge($array, $rel);

    //     //trace_log($array);
    //     return $array;
    // }
}
