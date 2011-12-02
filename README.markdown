# Jot

### Getting Started

#### Create Model

```
class Article_Model extends MY_Model {

}
```

#### Create Object

```
$article = new Article_Model;
$article->title = "Article Title";
$article->save();
```

#### Find Object

```
$article = $this->article_model->first();
echo $article->title;
// Returns "Article Title"
```

### Learn More
- [Attributes](https://github.com/tomkrush/Jot/wiki/attributes)
- [Associations](https://github.com/tomkrush/Jot/wiki/associations)
- [Calculations](https://github.com/tomkrush/Jot/wiki/calculations)
- [Finders](https://github.com/tomkrush/Jot/wiki/finders)
- [Persistance](https://github.com/tomkrush/Jot/wiki/persistance)
- [Validation](https://github.com/tomkrush/Jot/wiki/validation)

### License

Jot is released under the MIT license.