# Jot

What is Jot? Jot is a CodeIgniter Active Record solution developed specifically for people who want to make kick-ass web applications. Jot is influenced after Active Record from Ruby on Rails and Core Data from Cocoa. Why is Jot different from other ORM's in PHP. Jot is designed **for** the PHP syntax. It doesn't use clever magic to create an illusion that PHP is non-existant. PHP is a beautiful language and Jot proves it.

### Attributes
Jot is built around attributes. Attributes are an abstraction of table fields in a database. Two types of attributes are defined in Jot. **Persisted attributes** are saved to the database. In current iterations of Jot persisted attributes are not defined. The second type of attributes are transient. Transient attributes exist only the memory of a Jot object.

#### Reading Attributes

##### read_attribute($attribute)
Returns attribute if it exists in Jot object. This will not return attributes if they are not in Jot object.

	$name = $this->blog_model->read_attribute('name');

#### Writing Attributes (Does not persist automatically)

##### write_attribute($attribute, $value)
Writes attribute to Jot object.

	$this->blog_model->write_attribute('name', 'Blog');

##### assign_attributes($attributes)
Writes attributes to Jot object. Attributes must be an associative array.
	
	$this->blog_model->assign_attributes(array(
		'name' => 'Blog',
		'description' => 'lorem ipsum'
	));

#### Writing Attributes (Does persist automatically)

##### update_attribute($attribute, $value)
Writes attribute to Jot object and persists it. This will only persist if the attribute isn't transient. This will update all attributes changed, not just the one specified by function.

	$this->blog_model->update_attribute('name', 'Blog');

##### update_attribute($attributes)
Writes attributes to Jot object. Attributes must be an associative array. This will only persist if the attribute isn't transient. This will update all attributes changed, not just the one specified by function.
	
	$this->blog_model->update_attributes(array(
		'name' => 'Blog',
		'description' => 'lorem ipsum'
	));

#### Transient Attributes
Transient attributes are defined in **init** method on Jot object.

##### add_transient($attribute)
Sets a single transient attribute.

##### transient($attributes)
Sets an array of transient attributes.

### Persistance
Objects have the option to be persisted to a database. The methods **update** and **create** persist the object. The method destroy removes persistance.

All objects can be manipulated using attributes and persisted using the function **save**. 

### Finders

#### Methods

##### find($conditions, $offset, $limit)
Queries database using conditions. You can optionally set a range of rows you would like to return using offset and limit. Find returns an array of Jot objects.

	// Returns 10 blog objects with an offset of 2 where updated in last 2 weeks.
	$blog = $this->blog_model->find(array(
		'updated_at >' => strtotime('-2 weeks')
	), 2, 10);

##### first($conditions)
Returns first row found using conditions.

	$blog = $this->blog_model->first();

##### last($conditions)
Identical to first() with the exception that it returns the last row found.

	$blog = $this->blog_model->last();

##### all($conditions)
Finds all rows using conditions. Returns array of Jot objects.

	$blogs = $this->blog_model->all();

### Calculations
##### count($conditions)
Returns count of rows using conditions.

	$count = $this->blog_model->count();

##### average($attribute, $conditions)
Returns calculated average of all rows using conditions for attribute.

	$average = $this->blog_model->average('views');

##### sum($attribute, $conditions)
Returns calculated sum of all rows using conditions for attribute.

	$average = $this->blog_model->sum('views');

##### minimum($attribute, $conditions)
Returns calculated minimum of all rows using conditions for attribute.

	$minimum = $this->blog_model->minimum('rating');

##### maximum($attribute, $conditions)
Returns calculated maximum of all rows using conditions for attribute.

	$maximum = $this->blog_model->maximum('views');

#### Additional Condition Syntaxes

#### Primary Key
A condition that is only numeric is treated as the primary key.

	// Returns array with single jot object that has id 1.
	$blogs = $this->blog_model->find(1);
	
A condition that is an indexed array is treated as WHERE In primary key.

	// Returns array with 3 jot objects.
	$blogs = $this->blog_model->find(array(1, 3, 4));

#### WHERE IN
A condition typically uses a string or numeric datatype for the value. To produce WHERE IN an array can be used.

	$blogs = $this->blog_model->find('type' =>array('draft', 'pending'));

##### Conditions can set other properties of a SQL query.

	// Returns 10 blog objects with an offset of 2 where updated in last 2 weeks.
	$blog = $this->blog_model->find(array(
		'conditions' => array(
			'updated_at >' => strtotime('-2 weeks')
		),
		'limit' => 10,
		'offset' =>1
	));
	
	// Returns 10 blog objects on page 1 where updated in last 2 weeks with order of updated_at descending.
	$blog = $this->blog_model->find(array(
		'conditions' => array(
			'updated_at' => strtotime('-2 weeks')
 		),
		'limit' => 10,
		'page' => 1,
		'order' => 'updated_at DESC'
	));
	


### Hooks
Hooks enable models that extends Jot to provide custom functionality such as revisions, notifications, and calculations.

#### Hook Syntax
The preferred place to attach hooks is the **init** method. The only argument is the method that is called when the hook runs.

	$this->before_create('name_of_function');

#### Hook Example

	class Blog_Model extends MY_Model
	{
		public function init()
		{
			$this->before_save('post_notification');
		}
		
		protected function post_notification()
		{
			$this->notification_model->create('Blog saved!');
		}
	}

#### Available Hooks

##### before_create
Called before Jot persists object to database for first time.

##### after_create
Called after Jot persists object to database for first time.

##### before_update
Called before Jot persists object changes to database.

##### after_update
Called after Jot persists object changes to database.

##### before_save
Called before Jot persists object to database.

##### after_create
Called after Jot persists object to database.

##### before_validation
Called before Jot validates object.

##### after_validation
Called after Jot validates object.

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
	
### File Attachments
File attachments allow Jot objects to abstract files and treat them as attributes.

#### Getting Started

To attach a file in a jot model;

	class Person_Model extends MY_Model
	{
		public function init()
		{
			$this->has_attached_file('avatar');
		}
	}
	
Four new fields are required in your migration.

	create_table('person', array(
		...
		
		array('name'=>'avatar_file_name', 'type'=>'string'),
		array('name'=>'avatar_content_type', 'type'=>'string'),
		array('name'=>'avatar_file_size', 'type'=>'integer'),
		array('name'=>'avatar_updated_at', 'type'=>'integer'),
		MIGRATION_TIMESTAMPS
	));
	
To attach a file use the file_field in your form.

	<?php print form_for($f, $person, site_url('people/create'), array('multipart' => TRUE)); ?>
		<?php print $f->file_field('avatar'); ?>
	<?php print form_end(); ?>

To access your file in your view use url.
	<?=$person->avatar->url?>

#### has_attached_file options

	$this->has_attached_file('avatar', array(
		'path' => 'assets/avatars',
		'url' => 'assets/avatars'
	));

- **path** Server side path Jot uses to locate attachment. (default: assets/files)
- **url** Client side path that is used to locate attachment. (default: assets/files)

Note: If you change the url, you must also change the path.

#### Validators

##### attachment_required
The validation method 'attachment_required' validates **true** only if the attachment is present.

	class Person_Model extends My_Model
	{
		public function init()
		{
			$this->has_attached_file('avatar');
			
			$this->validates('avatar', 'attachment_required');
		}
	}

##### attachment_content_type
The validation method 'attachment_content_type' validates **true** only if the file content type matches specified types.

	class Person_Model extends My_Model
	{
		public function init()
		{
			$this->has_attached_file('avatar');
			
			$this->validates('avatar', array('attachment_content_type' => 'image/jpeg'));
		}
	}

or

	class Person_Model extends My_Model
	{
		public function init()
		{
			$this->has_attached_file('avatar');
		
			$this->validates('avatar', array('attachment_content_type' => array(
				'image/jpeg', 
				'image/png'
			)));
		}
	}

### Associations

### Serialization
Serializing allows Jot objects to extend their state across multiple page loads.

Note: To unserialize a jot object the model **must** be loaded. Not loading the model will cause fatal errors because PHP will attempt to create a object without the class.

	serialize($blog); // Outputs string
	unserialize($blog); // Outputs jot object

### Forms