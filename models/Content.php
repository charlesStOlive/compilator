<?php namespace Waka\Compilator\Models;

use Model;
use Session;

/**
 * Content Model
 */
class Content extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'waka_compilator_contents';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = ['data'];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'sector' => ['Waka\Crsm\Models\Sector'],
        'bloc' => ['Waka\Compilator\Models\Bloc'],
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    /**
     * Lists
     */
    public function listNestedSector()
    {
        return \Waka\Crsm\Models\Sector::all()->listsNested('name', 'id');
    }

    public function filterFields($fields, $context = null)
    {
        //trace_log('context : '. $context);
        if (($context == 'createBase') || ($context == 'updateBase')) {
            $fields->sector->hidden = true;
        }
        if (($context == 'createVersion') || ($context == 'updateVersion')) {
            $fields->sector->hidden = false;
        }
    }

    public function listDataSourceMediaList()
    {
        $blocId = Session::pull('manage_bloc_id_for_mediaList');
        $dataSource = Bloc::find($blocId)->document->data_source;
        return $dataSource->getImagesList($dataSource->test_id);

    }
}
