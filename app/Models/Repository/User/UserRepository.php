<?php namespace App\Models\Repository\User;

use App\Models\Repository\User\IUserRepository;
use App\Models\Repository\Repository as AbstractRepository;


class UserRepository extends AbstractRepository implements IUserRepository {

	protected $modelClassName = 'App\Models\User';

    public function getUserByEmail($email){
        return $this->model->where('email', $email)->first();
    }
}
