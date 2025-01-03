<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class ApiToken extends Model
{
  use Prunable;

  const TOKEN_NOT_FOUND_MESSAGE = "The token is not found. Make sure to request a new token using \"/api/token\"";
  const TOKEN_EXPIRED_MESSAGE = "The token is expired";

  const TABLE_NAME = "api_tokens";
  const TOKEN = "token";
  const TOKEN_ID = "token_id";
  const IS_USED_ALREADY = "is_used_already";
  const CREATED_AT = "created_timestamp";
  const UPDATED_AT = "updated_timestamp";
  const TOKEN_LENGTH = 128;
  const EXPIRED_IF_AFTER_40_MINUTES = 40;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = "api_tokens";

  /**
   * The primary key associated with the table.
   *
   * @var string
   */
  protected $primaryKey = "token_id";

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [self::TOKEN];

  /**
   * The storage format of the model"s date columns.
   *
   * @var string
   */
  protected $dateFormat = "U";

  /**
   * Cast created_timestamp to Carbon datetime.
   *
   * @var array
   */
  protected $casts = [
    self::CREATED_AT => "datetime",
  ];

  function __construct()
  {
    parent::__construct();
    $this->token = Str::random(self::TOKEN_LENGTH);
    $this->is_used_already = false;
  }

  public function prunable(): Builder
  {
    return static::where(self::CREATED_AT, "<=", now()->addMinutes(self::EXPIRED_IF_AFTER_40_MINUTES)->timestamp);
  }

  /**
   * token
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function token(): Attribute
  {
    return Attribute::make(
      get: fn($value) => ($value),
    );
  }

  /**
   *  is token used already
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function is_used_already(): Attribute
  {
    return Attribute::make(
      get: fn($value) => ($value),
      set: fn(bool $value) => ($value),
    );
  }

  /**
   * Query for a given token.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @param  string  $token
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeAsToken($query, $token)
  {
    return $query->where(self::TOKEN, $token);
  }

  /**
   * Query for not expired token"s lifetime.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeNotExpired($query)
  {
    return $query
      ->where(
        self::CREATED_AT,
        ">=",
        now()->subMinutes(self::EXPIRED_IF_AFTER_40_MINUTES)->timestamp
      )
      ->where(self::IS_USED_ALREADY, "=", false);
  }
}
