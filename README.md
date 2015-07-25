Laravel Auto Presenter 4
========================

[![StyleCI](https://styleci.io/repos/12034701/shield)](https://styleci.io/repos/12034701)
[![Build Status](https://img.shields.io/travis/laravel-auto-presenter/laravel-auto-presenter/master.svg?style=flat-square)](https://travis-ci.org/laravel-auto-presenter/laravel-auto-presenter)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/mccool/laravel-auto-presenter.svg?style=flat-square)](https://packagist.org/packages/mccool/laravel-auto-presenter)
[![Latest Version](https://img.shields.io/github/release/laravel-auto-presenter/laravel-auto-presenter.svg?style=flat-square)](https://github.com/laravel-auto-presenter/laravel-auto-presenter/releases)

This package automatically decorates objects bound to views during the view render process.


## Features

- Automatically decorate objects bound to views
- Automatically decorate objects within paginator instances
- Automatically decorate objects within arrays and collections


## Installing

You should install this package with [Composer](http://getcomposer.org/). Add the following "require" to your `composer.json` file and run the `composer install` command to install it.

```json
{
    "require": {
        "mccool/laravel-auto-presenter": "~4.0"
    }
}
```

Then, in your `config/app.php` add this line to your 'providers' array.

```php
'McCool\LaravelAutoPresenter\AutoPresenterServiceProvider',
```


## Usage

To show how it's used, we'll pretend that we have an Eloquent Post model. It doesn't have to be Eloquent, it could be any kind of class. But, this is a normal situation. The Post model represents a blog post.

I'm using really basic code examples here, so just focus on how the auto-presenter is used and ignore the rest.

```php
use Example\Accounts\User;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['author_id', 'title', 'content', 'published_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
```

Also, we'll need a controller..

```php
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

class PostsController extends Controller
{
    public function getIndex()
    {
        $posts = Post::all();
        return View::make('posts.index', compact('posts'));
    }
}
```

and a view...

```twig
@foreach($posts as $post)
    <li>{{ $post->title }} - {{ $post->published_at }}</li>
@endforeach
```

In this example the published_at attribute is likely to be in the format: "Y-m-d H:i:s" or "2013-08-10 10:20:13". In the real world this is not what we want in our view. So, let's make a presenter that lets us change how the data from the Post class is rendered within the view.

```php
use Carbon\Carbon;
use McCool\LaravelAutoPresenter\BasePresenter;

class PostPresenter extends BasePresenter
{
    public function __construct(Post $resource)
    {
        $this->wrappedObject = $resource;
    }

    public function published_at()
    {
        $published = $this->wrappedObject->published_at;

        return Carbon::createFromFormat('Y-m-d H:i:s', $published)
            ->toFormattedDateString();
    }
}
```

Here, the automatic presenter decorator is injecting the Post model that is to be decorated. **Please be aware that the constructor parameter should always be named `$resource` to allow Laravel's IoC container to correctly resolve the dependency.**

We need the post class to implement the interface.

```php
use Example\Accounts\User;
use Example\Blog\PostPresenter;
use McCool\LaravelAutoPresenter\HasPresenter;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements HasPresenter
{
    protected $table = 'posts';
    protected $fillable = ['author_id', 'title', 'content', 'published_at'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getPresenterClass()
    {
        return PostPresenter::class;
    }
}
```

Now, with no additional changes our view will show the date in the desired format.

### Array Access

If you want to access your presenter methods as array keys, add the `ArrayAccessTrait` to your *presenter* and make sure it implements the `ArrayAccess` interface.

```php
use Carbon\Carbon;
use McCool\LaravelAutoPresenter\BasePresenter;

use ArrayAccess;
use McCool\LaravelAutoPresenter\Traits\ArrayAccessTrait;

class PostPresenter extends BasePresenter implements ArrayAccess
{
       use ArrayAccessTrait;
...
```

That will now let you access your `published_at` method as...

```twig
@foreach($posts as $post)
    <li>{{ $post['title'] }} - {{ $post['published_at']' }}</li>
@endforeach
```

### Serialization

If you want your presenter values to be available when the object has been serialized, setup your presenter as outlined in Array Access above and add the `SerializesPresentedValuesTrait` to your *model* along with a protected array called `presented` with the name of the methods you want to be included.

```php
use Example\Accounts\User;
use Example\Blog\PostPresenter;
use McCool\LaravelAutoPresenter\HasPresenter;
use Illuminate\Database\Eloquent\Model;

use McCool\LaravelAutoPresenter\Traits\SerializesPresentedValuesTrait;

class Post extends Model implements HasPresenter
{

    use SerializesPresentedValuesTrait;


    protected presented = ['published_at'];
...
```

Now you can use those decorated values in queued jobs, like `Mail::queue`. This setup adds the benefit of being able to use the same view for queued emails and rendering views for the browser.


## Troubleshooting

If an object isn't being decorated correctly in the view then there's a good chance that it's simply not in existence when the view begins to render. For example, lazily-loaded relationships won't be decorated. You can fix this by eager-loading them instead. Auth::user() will never be decorated. I prefer to bind $currentUser to my views, anyway.


## License

Laravel Auto Presenter is licensed under [The MIT License (MIT)](LICENSE).
