<?php namespace Waka\Compilator\Classes;

use ApplicationException;
use Flash;
use Lang;
use Redirect;
use Storage;
use Waka\Compilator\Models\Bloc;
use Waka\Compilator\Models\BlocType;
use Waka\Compilator\Models\Document;
use \PhpOffice\PhpWord\TemplateProcessor;

class WordProcessor
{

    public $document_id;
    public $document;
    public $templateProcessor;
    public $bloc_types;
    public $AllBlocs;
    public $increment;
    public $blocFormatAccepted;
    public $dataSourceName;
    public $sector;
    public $apiBlocs;
    public $dotedValues;
    public $originalTags;
    public $nbErrors;

    public function __construct($document_id)
    {
        $this->prepareVars($document_id);
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
    }
    public function prepareVars($document_id)
    {
        $this->increment = 1;
        $this->nbErrors = 0;
        $this->document_id = $document_id;
        $this->bloc_types = BlocType::get(['id', 'code']);
        $this->AllBlocs = Bloc::get(['id', 'document_id', 'code', 'name']);
        //
        $document = Document::find($document_id);
        $this->document = $document;
        //
        $document_path = $this->getPath($this->document);

        $this->templateProcessor = new TemplateProcessor($document_path);
        // tous les champs qui ne sont pas des blocs ou des raw devront avoir ce type
        $this->dataSourceName = snake_case($document->data_source->model);
        $this->blocFormatAccepted = ['row', 'bloc', 'imagekey', $this->dataSourceName];
    }
    /**
     *
     */
    public function checkTags()
    {
        $allTags = $this->filterTags($this->templateProcessor->getVariables());
        $create = $this->createBlocs($allTags['blocs']);
        return $allTags;
    }
    /**
     *
     */
    public function filterTags($tags)
    {
        $this->deleteInform();
        //tablaux de tags pour les blocs, les injections et les rows
        $blocs = [];
        $injections = [];
        $imageKeys = [];
        $rows = [];
        $insideBlock = false;
        foreach ($tags as $tag) {
            // Si un / est détécté c'est une fin de bloc. on enregistre pas ce bloc.
            if (starts_with($tag, '/')) {
                $insideBlock = false;
                continue;
            }
            // si on est dans un bloc on recherche  une value ou un path.
            if ($insideBlock) {
                //array_push($logInfo, $blocType.".".$blocCode." à le tag : ".$tag);
                continue;

            }
            $parts = explode('.', $tag);
            if (count($parts) <= 1) {

                $this->recordInform('problem', Lang::get('waka.compilator::lang.word.processor.bad_format') . ' : ' . $tag);
                continue;
            }
            //trace_log($tag);
            $blocFormat = array_shift($parts);
            //trace_log("blocformat : " . $blocFormat);
            $blocType = array_shift($parts);
            $blocCode = implode(".", $parts);
            //
            if (!in_array($blocFormat, $this->blocFormatAccepted)) {
                $this->recordInform('problem', Lang::get('waka.compilator::lang.word.processor.bad_tag') . ' : ' . implode(", ", $this->blocFormatAccepted) . ' => ' . $tag);
                continue;
            }
            if ($blocFormat == $this->dataSourceName) {
                $tagOK = $this->checkInjection($tag);
                if ($tagOK) {
                    array_push($injections, $tag);
                }

                continue;
            }
            if ($blocFormat == 'imagekey') {
                array_push($imageKeys, $tag);
                continue;
            }
            if (!$blocType || !$blocCode) {
                $this->recordInform('warning', Lang::get('waka.compilator::lang.word.processor.bad_format') . ' : ' . $tag);
                continue;
            }
            if (!$this->returnBlocTypeId($blocType)) {
                $this->recordInform('warning', Lang::get('waka.compilator::lang.word.processor.type_not_exist') . ' : ' . $tag);
                continue;
            }
            if ($blocFormat == 'bloc') {
                // on commence un bloc
                $insideBlock = true;
            }

            $obj = (object) ['format' => $blocFormat, 'type' => $blocType, 'code' => $blocCode];
            array_push($blocs, $obj);
        }
        return [
            'blocs' => $blocs,
            'injections' => $injections,
            'imagekeys' => $imageKeys,
        ];
    }
    /**
     *
     */
    public function checkInjection($tag)
    {
        $ModelVarArray = $this->document->data_source->getDotedValues();
        if (!array_key_exists($tag, $ModelVarArray)) {
            $this->recordInform('problem', Lang::get('waka.compilator::lang.word.processor.field_not_existe') . ' : ' . $tag);
            return false;
        } else {
            return true;
        }
    }
    /**
     *
     */
    public function createBLocs($blocs)
    {
        if (count($this->document->blocs) == 0) {
            foreach ($blocs as $bloc) {
                $this->createBloc($bloc);
            }
        } else {
            // Bloc existe
            $i = 1;
            foreach ($blocs as $bloc) {
                $blocModel = $this->returnBlocModel($bloc);
                if (!$blocModel) {
                    $this->createBloc($bloc);
                } else {
                    $blocModel->sort_order = $this->increment++;
                    $blocModel->ready = 'ok';
                    $blocModel->save();
                }
                $i++;
            }
        }
    }
    public function createBloc($_bloc)
    {
        $bloc = new Bloc();
        $bloc->code = $_bloc->code;
        $bloc->sort_order = $this->increment++;
        $bloc->bloc_type_id = $this->returnBlocTypeId($_bloc->type);
        $bloc->name = $_bloc->code;
        $bloc->ready = 'ok';
        $this->document->blocs()->add($bloc);
    }
    public function returnBlocModel($_bloc)
    {
        $type_id = $this->returnBlocTypeId($_bloc->type);
        $blocExiste = Bloc::where('document_id', '=', $this->document_id)
            ->where('code', '=', $_bloc->code)
            ->where('bloc_type_id', '=', $type_id)->first();
        return $blocExiste;
    }

    public function returnBlocTypeId($name)
    {
        $blocTypes = $this->bloc_types;
        $id = null;
        foreach ($blocTypes as $blocType) {
            if ($blocType->code == $name) {
                $id = $blocType->id;
            }

        }
        return $id;
    }
    /**
     *
     */
    public function getPath($document)
    {
        if (!isset($document)) {
            throw new ApplicationException(Lang::get('waka.compilator::lang.word.processor.id_not_exist'));
        }

        $existe = Storage::exists('media' . $document->path);
        if (!$existe) {
            throw new ApplicationException(Lang::get('waka.compilator::lang.word.processor.document_not_exist'));
        }

        return storage_path('app/media' . $document->path);
    }

    public function recordInform($type, $message)
    {
        $this->nbErrors++;
        $this->document->record_inform($type, $message);
    }
    public function errors()
    {
        return $this->document->has_informs();
    }
    public function checkDocument()
    {
        $this->checkTags();
        if ($this->nbErrors > 0) {
            Flash::error(Lang::get('waka.compilator::lang.word.processor.errors'));
        } else {
            Flash::success(Lang::get('waka.compilator::lang.word.processor.success'));
        }
        return Redirect::refresh();
    }
    public function deleteInform()
    {
        $this->document->delete_informs();
    }
}
