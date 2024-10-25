<?php namespace App\Models\Repository;

interface IRepositoryInterface
{
	public function all($columns = array('*'));
	public function find($id, $columns = array('*'));
	public function delete($ids);
	public function destroy($ids);
    public function forceDelete($ids);
    public function first($columns = array('*'));
	public function findWhere( array $where , $columns = array('*') );
	public function update( array $attributes, $id );
    public function create( array $attributes );
    public function count();
    public function paginate($limit = null, $columns = array('*'));
    public function simplePaginate($limit = null, $columns = array('*'));

    /* Custom */
    public function toSql();
    public function scopeQuery(\Closure $scope);
    public function getCriteria();
    public function pushCriteria(ICriteria $criteria);
    public function getByCriteria(ICriteria $criteria);
    public function clearCriteria();
    public function orderBy($column, $direction = 'asc');
    public function with($relations);
    public function getModel();
}