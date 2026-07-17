<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User model representing an application user and API client.
 *
 * This Eloquent model represents a user in the system with authentication capabilities.
 * Users can obtain API tokens via Sanctum for stateless bearer token authentication.
 * The model supports password hashing, notifications, and API token management.
 * Use #[Fillable] and #[Hidden] attributes to define mass-assignable and hidden properties.
 *
 * @property int $id The user's unique identifier (primary key)
 * @property string $name The user's display name
 * @property string $email The user's email address (unique)
 * @property string $password The hashed password
 * @property \Illuminate\Support\Carbon|null $email_verified_at Timestamp when email was verified (nullable)
 * @property \Illuminate\Support\Carbon $created_at Timestamp when the user was created
 * @property \Illuminate\Support\Carbon $updated_at Timestamp when the user was last updated
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
