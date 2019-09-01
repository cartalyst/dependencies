## Usage

In this section we'll show how you can make use of the extensions package.

### Basic Usage

```php
use Cartalyst\Dependencies\DependencySorter;

$sorter = new DependencySorter();

// Add a "foo" item who depends on "bar" and "baz"
$sorter->add('foo', ['bar', 'baz']);
$sorter->add('baz');
$sorter->add('bar', 'baz');

var_dump($sorter->sort()); // Will spit out ['baz', 'bar', 'foo']

// Circular dependencies are recognized and an exception
// will be thrown rather than a never-ending sorting loop.
$sorter->add('foo', ['bar', 'baz']);
$sorter->add('baz');
$sorter->add('bar', 'foo');
var_dump($sorter->sort());

// UnexpectedValueException: Item [foo] and [bar] have a circular dependency.
```

### DependentInterface

Our sorter also has the ability to take classes which implement Cartalyst\Dependencies\DependentInterface. Let's use the following, simplified asset example:

```php
use Cartalyst\Dependencies\DependencySorter;
use Cartalyst\Dependencies\DependentInterface;

class Asset implements DependentInterface
{
    protected $slug;

    protected $path;

    protected $dependencies = [];

    public function __construct(string $slug, string arra $path, $dependencies = [])
    {
        $this->slug = $slug;

        $this->path = $path;

        $this->dependencies = $dependencies;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}

// Queue assets in a Dependency Sorter instance
$sorter = new DependencySorter([
    new Asset('bootstrap', 'js/bootstrap-2.3.1.js', 'jquery'),
    new Asset('jquery', 'js/bootstrap-1.9.1.min.js'),
    new Asset('main', 'js/main.js', ['jquery', 'bootstrap']),
]);

$assets = $sorter->sort();

// In your view
@foreach ($assets as $asset)
    <script src="{{ $asset->getPath() }}"></script>
@endforeach
```
