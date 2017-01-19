Laravel Auto Presenter 5
========================

[![StyleCI Status](https://styleci.io/repos/12034701/shield)](https://styleci.io/repos/12034701)
[![Build Status](https://img.shields.io/travis/laravel-auto-presenter/laravel-auto-presenter/master.svg?style=flat-square)](https://travis-ci.org/laravel-auto-presenter/laravel-auto-presenter)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/mccool/laravel-auto-presenter.svg?style=flat-square)](https://packagist.org/packages/mccool/laravel-auto-presenter)
[![Latest Version](https://img.shields.io/github/release/laravel-auto-presenter/laravel-auto-presenter.svg?style=flat-square)](https://github.com/laravel-auto-presenter/laravel-auto-presenter/releases)

This package automatically decorates objects bound to views during the view render process.


## Features

- Automatically decorate objects bound to views
- Automatically decorate objects within paginator instances
- Automatically decorate objects within arrays and collections


## Upgrading

If you're upgrading from Laravel Auto Presenter 4, to 5, note that:

* The `BasePresenter` no longer has a constructor, so you cannot call `parent::__construct($resource)`.
* The model is now injected using the `setWrappedObject` method, inherited from the `BasePresenter`.
* V5 now supports Laravel 5.4 as well as 5.1, 5.2, and 5.3.


## Installing

Either [PHP](https://php.net) 5.5+ or [HHVM](http://hhvm.com) 3.6+ are required.

To get the latest version of Laravel Auto Presenter, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require mccool/laravel-auto-presenter
```

Instead, you may of course manually update your require block and run `composer update` if you so choose:

```json
{
    "require": {
        "mccool/laravel-auto-presenter": "^5.0"
    }
}
```

Then, in your `config/app.php` add this line to your 'providers' array.

```php
McCool\LaravelAutoPresenter\AutoPresenterServiceProvider::class,
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
use Example\Accounts\Post;
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
use Example\Accounts\Post;
use McCool\LaravelAutoPresenter\BasePresenter;

class PostPresenter extends BasePresenter
{
    public function published_at()
    {
        $published = $this->wrappedObject->published_at;

        return Carbon::createFromFormat('Y-m-d H:i:s', $published)
            ->toFormattedDateString();
    }
}
```

*Note that the model is injected by calling the `setWrappedObject` method, inherited from `BasePresenter`.*

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


## Security

If you discover a security vulnerability within this package, please send an e-mail to Graham Campbell at graham@alt-three.com. All security vulnerabilities will be promptly addressed.


## License

Laravel Auto Presenter is licensed under [The MIT License (MIT)](LICENSE).
