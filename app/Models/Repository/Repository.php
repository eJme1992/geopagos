<?php namespace App\Models\Repository;


use Illuminate\Container\Container as Application;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

use App\Models\Repository\IRepositoryInterface;
use App\Models\Repository\ICriteria;
use App\Models\Repository\RepositoryException;

abstract class Repository implements IRepositoryInterface {	

	/**
     * Model Class Name
     *
     * @var Class
     */
	protected $modelClassName;

    /**
     * @var Model
     */
    protected $model;

    /**
     * Collection of Criteria
     *
     * @var Collection
     */
    protected $criteria;

    /**
     * @var \Closure
     */
    protected $scopeQuery = null;

    /**
     * @var Connection
     */
    protected $connection = null;

    public function __construct()
    {
        $this->criteria = new Collection();
        $this->makeModel();
        //$this->model = new $this->modelClassName;
    }

    /**
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = new $this->modelClassName;
        $this->scopeQuery = null;

        if (!$model instanceof Model)
            throw new RepositoryException("Class {$this->modelClassName} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $model;
    }

	/**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
	{
		return call_user_func_array("{$this->modelClassName}::create", array($attributes));
	}

	public function all($columns = array('*'))
	{
		$this->applyCriteria();
        $this->applyScope();

        if ( $this->model instanceof \Illuminate\Database\Eloquent\Builder ){
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }

        $this->resetModel();
        
        return $results;
	}

	public function find($id, $columns = array('*'))
	{
		$this->applyCriteria();
        $this->applyScope();
        
        $model = $this->model->findOrFail($id, $columns);
        $this->resetModel();
        return $model;
    }

    public function findNotFail($id, $columns = array('*'))
    {
        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->find($id, $columns);
        $this->resetModel();
        return $model;
    }

	public function delete($ids)
	{
		$this->applyScope();
        
        $model = $this->model->findOrFail($ids);
        //$originalModel = clone $model;

        $this->resetModel();
        $deleted = $model->delete();
        //event(new RepositoryEntityDeleted($this, $originalModel));
        return $deleted;
		//return call_user_func_array("{$this->modelClassName}::destroy", array($ids));
	}

    public function forceDelete($ids)
    {
        $this->applyScope();
        
        $model = $this->model->find($ids);

        $this->resetModel();
        $deleted = $model->forceDelete();
        
        return $deleted;
    }
	
	public function destroy($ids)
	{
		return call_user_func_array("{$this->modelClassName}::destroy", array($ids));
	}

    public function deleteAll()
    {
        $this->applyCriteria();
        $this->applyScope();

        $this->model->delete();

        $this->resetModel();
        
        return true;
    }

    public function first($columns = array('*'))
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->first($columns);
        $this->resetModel();
        return $model;
    }

	public function lists($column, $key = null)
	{
		$this->applyCriteria();
        $this->applyScope();
        $results = $this->model->lists($column, $key)->all();
        $this->resetModel();
		return $results;
	}

	public function findWhere(array $where , $columns = array('*') ){
		$this->applyCriteria();
        $this->applyScope();

		foreach ($where as $field => $value) {
            if ( is_array($value) ) {
                list($field, $condition, $val) = $value;
                $this->model = $this->model->where($field,$condition,$val);
            } else {
            	$this->model = $this->model->where($field,'=',$value);
            }
        }

        return $this->model->get($columns);
	}

	/**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param $id
     * @return mixed
     */
    public function update(array $attributes, $id){

        $this->applyScope();

        $model = $this->model->findOrFail($id);
        $model->fill($attributes);
        $model->save();

        $this->resetModel();

        //event(new RepositoryEntityUpdated($this, $model));
        return $model;
    }

    public function count()
	{
		$this->applyCriteria();
        $this->applyScope();

		return $this->model->count();
	}

	 /**
     * Retrieve all data of repository, paginated
     * @param null $limit
     * @param array $columns
     * @return mixed
     */
    public function paginate($limit = null, $columns = array('*'))
    {
        $this->applyCriteria();
        $this->applyScope();

        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        $result = $this->model->paginate($limit, $columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Retrieve all data of repository, paginated
     * @param null $limit
     * @param array $columns
     * @return mixed
     */
    public function simplePaginate($limit = null, $columns = array('*'))
    {
        $this->applyCriteria();
        $this->applyScope();

        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        $result = $this->model->simplePaginate($limit, $columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Retrieve all data of repository, paginated
     * @param null $limit
     * @param array $columns
     * @return mixed
     */
    public function grid($limit = null, $columns = array('*'), $force = false )
    {
        $this->applyCriteria();
        $this->applyScope();

        $topLimit = !$force ? env('EXPORT_LIMIT', 1000) : 999999999;

        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : ( $limit > $topLimit ? $topLimit : $limit );

        $result = $this->model->grid()->paginate($limit, $columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Query Scope
     *
     * @param \Closure $scope
     * @return $this
     */
    public function scopeQuery(\Closure $scope){
        $this->scopeQuery = $scope;
        return $this;
    }

    /**
     * Get Collection of Criteria
     *
     * @return Collection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Push Criteria for filter the query
     *
     * @param ICriteria $criteria
     * @return $this
     */
    public function pushCriteria(ICriteria $criteria)
    {
        $this->criteria->push($criteria);
        return $this;
    }

    public function clearCriteria()
    {
        $this->criteria = new Collection();
        return $this->criteria;
    }

    /**
     * Find data by Criteria
     *
     * @param ICriteria $criteria
     * @return mixed
     */
    public function getByCriteria(ICriteria $criteria)
    {
        $this->model = $criteria->apply($this->model, $this);
        return $this->model->get();
    }

    /**
     * Load relations
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * Set the "orderBy" value of the query.
     *
     * @param mixed  $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }


    /**
     * To SQL
     *
     * @return $this
     */
    public function toSql()
    {
        $this->applyCriteria();
        $this->applyScope();

        $query = $this->model->toSql();

        $this->resetModel();

        return $query;
    }

    /**
     * Apply scope in current Query
     *
     * @return $this
     */
    protected function applyScope()
    {
        if ( isset($this->scopeQuery) && is_callable($this->scopeQuery) ) {
            $callback = $this->scopeQuery;
            $this->model = $callback($this->model);
            $this->model->getModel()->setConnection( $this->connection );
        }
        return $this;
    }

    /**
     * Apply criteria in current Query
     *
     * @return $this
     */
    protected function applyCriteria()
    {
        $criteria = $this->getCriteria();
        if ( $criteria ) {
            foreach ($criteria as $c) {
                if ( $c instanceof ICriteria ) {
                	$this->model = $c->apply($this->model, $this);
                }
            }
        }
        return $this;
    }

    /**
     * To Change DATABASE
     *
     * @return $this
     */
    public function setConnection( $connectionName )
	{
        $this->connection = $connectionName;
        $this->model->setConnection( $connectionName );
    }

    public function getConnection()
	{
        return $this->model->getConnection();
    }

    /**
     * @throws RepositoryException
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    public function getModel()
    {
        return $this->model;
    }
}