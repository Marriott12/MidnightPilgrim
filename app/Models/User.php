<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
	// Minimal user model for policies and authentication placeholders.
	protected $fillable = [
		'name',
		'email',
		'password',
	];
}
