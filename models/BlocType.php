<?php namespace Waka\Compilator\Models;

use Model;
use Waka\Utils\Models\DataSource;

/**
 * BlocType Model
 */
class BlocType extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;
    use \Waka\Utils\Classes\Traits\IconsList;

    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'waka_compilator_bloc_types';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [
        'name' => 'required',
        'code' => 'required',
        'type' => 'required',
        'config' => 'required',
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

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
    public $hasMany = [
        'blocs' => ['Waka\Compilator\Models\Bloc'],
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [
        'icon_png' => 'System\Models\File',
        'src_explication' => 'System\Models\File',
    ];
    public $attachMany = [];

    public function listDataSource()
    {
        return DataSource::lists('name', 'id');
    }
}
