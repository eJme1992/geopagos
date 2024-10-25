<?php namespace App\Models\Repository\User;

use App\Models\Repository\IRepositoryInterface;

interface IUserRepository extends IRepositoryInterface
{
	public function getUserByEmail($email);
}
