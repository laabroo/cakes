<?php 

//App::uses('AuthComponent', 'Controller/Component');

class User extends AppModel {

    public $name = 'User';
    public $displayField = 'nama';
   public $validate = array(
		'nama'=>array(
			'Tolong masukan nama anda.'=>array(
				'rule'=>'notEmpty',
				'message'=>'Masukan nama anda.'
			)
		),
		'username'=>array(
			'Minimal karakter 5 - 15'=>array(
				'rule'=>array('between', 5, 15),
				'message'=>'Minimal karakter 5 - 15.'
			),
			'Username sudah terdaftar'=>array(
				'rule'=>'isUnique',
				'message'=>'Username sudah terdaftar.'
			)
		),
		'email'=>array(
			'Valid email'=>array(
				'rule'=>array('email'),
				'message'=>'Masukan email yang benar.'
			)
		),
		'password'=>array(
		    'Not empty'=>array(
		        'rule'=>'notEmpty',
		        'message'=>'Masukan password anda.'
		    ),
		    'Match passwords'=>array(
		        'rule'=>'matchPasswords',
		        'message'=>'Password anda tidak sama.'
		    )
		),
		'password_confirmation'=>array(
		    'Not empty'=>array(
		        'rule'=>'notEmpty',
		        'message'=>'Masukan kembali password anda.'
		    )
		)
	);
	
	public function matchPasswords($data) {
	    if ($data['password'] == $this->data['User']['password_confirmation']) {
	        return true;
	    }
	    $this->invalidate('password_confirmation', 'Your passwords do not match');
	    return false;
	}
	
	public function beforeSave() {
	    if (isset($this->data['User']['password'])) {
	        $this->data['User']['password'] = AuthComponent::password($this->data['User']['password']);
	    }
	    return true;
	}

}

?>
