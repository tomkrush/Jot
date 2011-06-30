# Jot

What is Jot? Jot is a CodeIgniter Active Record solution developed specifically for people who want to make kick-ass web applications. Jot is influenced after Active Record from Ruby on Rails and Core Data from Cocoa. Why is Jot different from other ORM's in PHP. Jot is designed **for** the PHP syntax. It doesn't use clever magic to create an illusion that PHP is non-existant. PHP is a beautiful language and Jot proves it.

### Attributes
#### Types
The core is built around an attributes API. There are two types of attributes.
- Persisted attributes are stored in a database.
- Transient attributes are temporarily stored in memory. These attributes are not stored to the database.
#### Functions
Custom methods and be used to set and get attributes from memory.

### Persistance
Objects have the option to be persisted to a database. The methods **update** and **create** persist the object. The method destroy removes persistance.

All objects can be manipulated using attributes and persisted using the function **save**. 

### Finders
##### first($conditions, $order)
##### last($conditions, $order)
##### all($conditions, $order)
##### find($conditions, $order, $offset, $limit)

### Calculations
##### count($conditions)
##### average($conditions)
##### sum($conditions)
##### minimum
##### maximum

### Hooks
##### before_create
##### after_create
##### before_update
##### after_update
##### before_save
##### after_create
##### before_validation
##### after_validation

### Validation
To ensure valid data is persisted to the database a validation can be created. Most validation cases can be handled using the included methods, but using a simple hook system new validations can be created.

Validations are run whenever an attempt to persist an object occurs.

Example, a simple site requires a blog. The blog has a name and requires a name. That is expressed with the following model.

	class Blog_Model extends My_Model {
		public function init()
		{
			$this->validate('name', 'required');
		}
	}

Creating a new blog automatically runs the validations. 

	$blog = new Blog_Model;
	$blog->save();
	// This object is not valid because there is no name.

	$blog = new Blog_Model;
	$blog->name = 'My Awesome Blog';
	$blog->save();
	// This object is valid because there is a name;

Sometimes you want to persist information to the database and skip validation. This can be done just as easily.

	$blog = new Blog_Model;
	$blog->save(FALSE); // False forces no validation

#### Validation Syntax
The method *validates* accepts several syntax styles depending on the complexity of the validation.

The lightest syntax works can only be used when there is only validation run on a property. This validation also **cannot** have any options.

	class Blog_Model extends My_Model {
		public function init()
		{
			$this->validates('name', 'required');
		}
	}

When using options or multiple validations a more configurable syntax can be utilized. 
	
	class Blog_Model extends My_Model {
		public function init()
		{
			$this->validates('name', array(
				'required', 
				'uniqueness' => array(
					'exclude_self' => TRUE 
				)
			);
		}
	}

#### Validation Methods
##### required
The validation method 'required' validates **true** only if the attribute is **NULL** or not existant.
 
	class Blog_Model extends My_Model {
		public function init()
		{
			$this->validates('name', 'required');
		}
	}

##### uniqueness
The validation method 'uniqueness' validates **true** only if the attribute value is uniquee in the specified scope. By default the scope includes all items in a table. A scope can be further specified by settings conditions or indicating that the scope should exclude the current object.

	class Blog_Model extends My_Model {
		public function init()
		{
			// Slug must be unique in entire table.
			$this->validates('slug', 'uniqueness');

			// Slug must be unique except when comparing to self.
			$this->validates('slug', array(
				'uniqueness'=>array(
					'exclude_self'=>TRUE
				)
			));

			// Slug must be unique inside scope.
			$this->validates('slug', array(
				'uniqueness'=>array(
					'scope'=>'category_id'
				)
			));
		}
	}

##### length
The validation method 'length' validates **true** only if the attribute value has a length inside of the specified range.

	class User_Model extends My_Model
	{
		public function init()
		{
			$this->validates('password', 'length'=>array(
				'minimum'=> 6,
				'maximum'=> 30
			));
		}
	}


##### confirm
The validation method 'confirm' validates **true** only if attribute value is equal to attribute_confirm.

	class User_Model extends My_Model
	{
		public function init()
		{	
			// Transient attribute must be created.
			// Jot will eventually create this attribute
			// automatically.
			$this->transient('confirm_password');

			// Password must be equal to attribute password_confirm
			$this->validates('password', 'confirm');
		}
	}

#### Creating Custom Validations
A hook system allows custom validation methods to be used. There are two separate function signatures that can be used to extend validations.

If a validation method is planned to be used in more than one model than a function helper should be used. Below is the syntax.

	function jot_validate_example($object, $attribute, $options)
	{
		if ( ... )
		{
			$object->add_error(array($attribute, $error_message));
			return FALSE;
		}

		return TRUE;	
	}

If a validation method is unique to a certain model than the method can be declared on the model class. Below is the syntax.

	class Blog_Model {
		...

		public function validate_example($object, $attribute, $options)
		{
			// Same as function helper
		}
	}

### Associations

### Serialization
Serializing allows Jot objects to extend their state across multiple page loads.

Note: To unserialize a jot object the model **must** be loaded. Not loading the model will cause fatal errors because PHP will attempt to create a object without the class.

	serialize($blog); // Outputs string
	unserialize($blog); // Outputs jot object

### Forms