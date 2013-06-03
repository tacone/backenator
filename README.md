# Backenator

ORM (Object Relational Mapper) build on top of Eloquent that maps REST resources.

[![Build Status](https://travis-ci.org/ellipsesynergie/backenator.png?branch=develop)](https://travis-ci.org/ellipsesynergie/backenator)

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
    protected $_fillable = array();

}
```

### Retrieving A Record By Primary Key

```php
$user = self::find(1); // GET http://api.example.com/users/1
```

### Querying

```php
$users = $this->addUriSegment('newest')->get(); // GET http://api.example.com/users/newest
$users = $this->addUriSegment('newest')->where('limit', 10)->get(); // GET http://api.example.com/users/newest?limit=10
$user = $this->addUriSegment(1)->first(); // GET http://api.example.com/users/1
```

### Creating

```php
$this->firstname = 'John';
$this->lastname = 'Doe';
$this->save(); // POST http://api.example.com/users
```

### Updating

```php
$user = self::find(1);
$user->firstname = 'Johnny';
$user->save(); // PUT http://api.example.com/users/1
```

### Deleting

```php
$this->addUriSegment(1)->delete(); // DELETE http://api.example.com/users/1
```

## Authors

[@ellipsesynergie](http://github.com/ellipsesynergie)

## License

MIT
