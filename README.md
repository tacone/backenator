# Backenator

ORM (Object Relational Mapper) build on top of Eloquent that maps REST resources.

[![Build Status](https://travis-ci.org/ellipsesynergie/backenator.png?branch=master)](https://travis-ci.org/ellipsesynergie/backenator)

## Documentation

### Config

Coming soon!

### Model

```php
class User extends Backenator {

    /**
     * Model's URI
     *
     * @var string
     */
    protected static $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array();

}
```

### Retrieving A Record By Primary Key

```php
$user = self::find(1); // GET http://api.example.com/users/1
```

### Querying

```php
$users = $this->segment('newest')->get(); // GET http://api.example.com/users/newest
$users = $this->segment('newest')->where('limit', 10)->get(); // GET http://api.example.com/users/newest?limit=10
$user = $this->segment(1)->first(); // GET http://api.example.com/users/1
```

### Creating

```php
$user = new User;
$user->firstname = 'John';
$user->lastname = 'Doe';
$user->save(); // POST http://api.example.com/users
```

### Updating

```php
$user = User::find(1);
$user->firstname = 'Johnny';
$user->save(); // PUT http://api.example.com/users/1
```

### Deleting

```php
$this->segment(1)->delete(); // DELETE http://api.example.com/users/1
```

## Authors

[@ellipsesynergie](http://github.com/ellipsesynergie)

## License

MIT
