<?php namespace Waka\Compilator\Classes;

use Waka\Compilator\Classes\ContentParser;
use Yaml;

class WordCreator extends WordProcessor
{

    private $dataSourceModel;
    private $dataSourceId;

    use \Waka\Cloudis\Classes\Traits\CloudisKey;

    public function prepareCreatorVars($dataSourceId)
    {
        $this->dataSourceModel = $this->linkModelSource($dataSourceId);
        $this->keyBlocs = $this->getKeyGroups('bloc');
        $this->keyRows = $this->getKeyGroups('row');
        $this->apiInjections = $this->getApiInjections();
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
        // if($this->errors()) {
        //     Flash::error(Lang::get('waka.compilator::lang.word.processor.errors'));
        //     return Redirect::back();
        // }
        //Traitement des champs simples
        foreach ($originalTags['injections'] as $injection) {
            $value = $this->apiInjections[$injection];
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
        //$this->templateProcessor->cloneRowAndSetValues('row.name.userId', $data);
        //Traitement des BLOCS | je n'utilise pas les tags d'origine mais les miens.
        foreach ($this->keyBlocs as $key => $rows) {
            $count = count($rows);
            //trace_log("foreach---------------------------".$key.' count '.$count);
            $this->templateProcessor->cloneBlock($key, $count, true, true);
            $i = 1;
            foreach ($rows as $row) {
                // //trace_log($row);
                //trace_log("--------foreachkey------------------------");
                foreach ($row as $cle => $data) {
                    //trace_log($cle.'#'.$i);
                    //trace_log($data);
                    if (starts_with($cle, 'image')) {
                        if ($data) {
                            $this->templateProcessor->setImageValue($cle . '#' . $i, $data, 1);
                        }

                    } else {
                        $this->templateProcessor->setValue($cle . '#' . $i, $data, 1);
                    }

                }
                $i++;
            }
        }
        //trace_log($this->keyRows);
        //Traitement des ROWS | je n'utilise pas les tags d'origine mais les miens.
        foreach ($this->keyRows as $key => $rows) {
            $count = count($rows);
            //trace_log("foreach---------------------------".$key.' count '.$count);
            $this->templateProcessor->cloneRow($key, $count);
            //trace_log('all tags-------***');
            //trace_log($this->templateProcessor->getVariables());
            //trace_log('end all tags-------');
            $i = 1;
            foreach ($rows as $row) {
                //trace_log($row);
                //trace_log("--------foreachkey------------------------");
                foreach ($row as $cle => $data) {
                    //trace_log($cle.'#'.$i);
                    //trace_log($data);
                    if (starts_with($cle, 'row.image')) {
                        //trace_log("c'est une image");
                        $this->templateProcessor->setImageValue($cle . '#' . $i, $data);
                    } else {
                        //trace_log("c'est PAS img");
                        $this->templateProcessor->setValue($cle . '#' . $i, $data);
                    }

                }
                $i++;
            }
        }
        $name = str_slug($this->document->name . '-' . $this->dataSourceModel->name);
        $coin = $this->templateProcessor->saveAs($name . '.docx');
        return response()->download($name . '.docx')->deleteFileAfterSend(true);
    }

    public function getKeyGroups($type = null)
    {
        $doc = $this->document;
        //On filtre les blocs par type row ou bloc;
        $blocs = $doc->blocs()->whereHas('bloc_type', function ($query) use ($type) {
            $query->where('type', $type);
        })->get();

        $compiledBlocs = [];
        foreach ($blocs as $bloc) {
            $tag = $this->rebuildTag($bloc);
            //$datas = $this->launchCompiler($bloc);

            $data = Yaml::parse($bloc->bloc_type->config);

            $blocModel = $data['model'] ?? false;
            $relation = $data['relation'] ?? false;

            $parser = new ContentParser();
            $parser->setModel($blocModel, $this->dataSourceModel);
            $parser->setOptions($bloc->bloc_form);

            $fields = $data['fields'] ?? false;
            //trace_log("----------fields-----------");
            //trace_log($fields);
            $resultat = $parser->parseFields($fields);
            //trace_log("Resultat process Static config");
            //trace_log($resultat);
            //return $resultat;
            //
            $compiledBlocs[$tag] = $resultat;
        }
        return $compiledBlocs;
    }

    public function getApiInjections()
    {
        return $this->document->data_source->listApi($this->dataSourceId);
    }

    private function rebuildTag($bloc)
    {
        $blocType = $bloc->bloc_type;
        $tag = $blocType->type . '.' . $blocType->code . '.' . $bloc->code;
        return $tag;
    }

}
