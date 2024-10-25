<?php namespace App\Models\Repository;

use App\Models\Repository\IRepositoryInterface;

/**
 * Interface ICriteria
 * @package Wayni\Domain
 */
interface ICriteria
{
    /**
     * Apply criteria in query repository
     *
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, IRepositoryInterface $repository);
}