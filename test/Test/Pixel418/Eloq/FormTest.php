<?php

namespace Test\Pixel418\Eloq;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Pixel418\Eloq\Stack\Util\Form;

echo 'Eloq ' . \Pixel418\Eloq::VERSION . ' tested with ';

class FormTest extends \PHPUnit_Framework_TestCase
{

    public function getLoginForm()
    {
        return (new Form)
            ->addInput('username')
            ->addInput('password')
            // A hack to allow $_POST data simulation
            ->setPopulation( $_POST );
    }


    /* BASIC TEST METHODS
     *************************************************************************/
    public function testNewInstance()
    {
        $form = (new Form);
        $this->assertTrue(is_a($form, 'Pixel418\\Eloq\\Stack\\Util\\Form'), 'Form must be an object');
    }

    public function testEmptyForm()
    {
        $form = $this->getLoginForm();
        $this->assertFalse($form->isActive(), 'Form must be inactive');
    }

    public function testInactiveForm()
    {
        $_POST = ['unknownEntry'=>'someValue'];
        $form = $this->getLoginForm();
        $this->assertFalse($form->isActive(), 'Form must be inactive');
    }

    public function testActiveFullForm()
    {
        $_POST = ['username'=>'tzi', 'password'=>'secret'];
        $form = $this->getLoginForm();
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
    }

    public function testActivePartialForm()
    {
        $_POST['username'] = 'roose.bolton';
        $form = $this->getLoginForm();
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
    }


    /* FORM INPUT TEST METHODS
     *************************************************************************/
    public function testInactiveForm_NoValues()
    {
        $form = $this->getLoginForm();
        $form->treat();
        $this->assertNull($form->username, 'Input has a NULL value');
        $this->assertNull($form->getInputError('username'), 'Input has no error');
    }

    public function testInactiveForm_DefaultValues()
    {
        $defaultValue = 'thoros.de.myr';
        $form = $this->getLoginForm();
        $form->treat();
        $form->setInputDefaultValue('username', $defaultValue);
        $this->assertEquals($defaultValue, $form->username, 'Input has the default value');
        $this->assertNull($form->getInputError('username'), 'Input has no error');
    }

    public function testActiveForm_FetchValues()
    {
        $_POST['username'] = 'beric.dondarrion';
        $form = $this->getLoginForm();
        $form->treat();
        $this->assertEquals($_POST['username'], $form->username, 'Input has the fetch value');
        $this->assertNull($form->getInputError('username'), 'Input has no error');
        $this->assertNull($form->password, 'Input has a NULL value');
        $this->assertNull($form->getInputError('password'), 'Input has no error');
    }


    /* REQUIRED TEST METHODS
     *************************************************************************/
    public function testRequiredEntry_Null()
    {
        $_POST['username'] = 'balon.greyjoy';
        $form = $this->getLoginForm()
            ->addInputFilter('password', 'required');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertNull($form->password, 'Non-existing form entry is null');
        $this->assertEquals('required', $form->getInputError('password'), 'One error must be thrown, the password field must be required');
    }

    public function testRequiredEntry_Empty()
    {
        $_POST['username'] = 'yara.greyjoy';
        $_POST['password'] = '';
        $form = $this->getLoginForm()
            ->addInputFilter('password', 'required');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals('', $form->password, 'The password fetched value must be an empty string');
        $this->assertEquals('required', $form->getInputError('password'), 'One error must be thrown, the password field must be required');
    }

    public function testRequiredEntry_Given()
    {
        $_POST['username'] = 'talisa.maegyr';
        $_POST['password'] = 'secret';
        $form = $this->getLoginForm()
            ->addInputFilter('password', 'required');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($_POST['password'], $form->password, 'The password fetched value must be the given string');
        $this->assertNull($form->getInputError('password'), 'No error must be thrown');
    }


    /*************************************************************************
    MAX & MIN LENGTH TEST METHODS
     *************************************************************************/
    public function testMaxLengthEntry_Nok()
    {
        $_POST['username'] = 'margaery.tyrell'; // username length: 15
        $form = $this->getLoginForm()
            ->addInputFilter('username', 'maxLength:14');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals('maxLength', $form->getInputError('username'), 'One error must be thrown, the username field must be too long');
    }

    public function testMaxLengthEntry_Ok()
    {
        $_POST['username'] = 'olenna.tyrell'; // username length: 13
        $form = $this->getLoginForm()
            ->addInputFilter('username', 'maxLength:13');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testMinLengthEntry_Nok()
    {
        $_POST['username'] = 'mance.raider'; // username length: 12
        $form = $this->getLoginForm()
            ->addInputFilter('username', 'minLength:13');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals('minLength', $form->getInputError('username'), 'One error must be thrown, the username field must be too short');
    }

    public function testMinLengthEntry_Ok()
    {
        $_POST['username'] = 'brienne.de.torth'; // username length: 16
        $form = $this->getLoginForm()
            ->addInputFilter('username', 'minLength:16');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }


    /*************************************************************************
    PHP FILTER TEST METHODS
     *************************************************************************/
 /*   public function testPHPfilter_SanitizeStripTag_AsId()
    {
        $username = 'tzi<script>';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addFilter('username', FILTER_SANITIZE_STRING);
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as valid');
        $this->assertEquals('tzi', $form->get('username'), 'Sanitize script tag');
    }

    public function testPHPfilter_SanitizeStripTag_AsName()
    {
        $username = 'tzi<script>';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addFilter('username', 'string');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as valid');
        $this->assertEquals('tzi', $form->get('username'), 'Sanitize script tag');
    }

    public function testPHPfilter_ValidateEmail_Nok()
    {
        $username = 'tzi';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addFilter('username', 'validate_email');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals('tzi', $form->get('username'), 'Non-valid email form entry is intact');
        $this->assertEquals(1, count($form->getErrors('username')), 'One error message for non-valid email entry');
    }

    public function testPHPfilter_ValidateEmail_Ok()
    {
        $username = 'tzi@domain.tld';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addFilter('username', 'validate_email');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as valid');
        $this->assertEquals($username, $form->get('username'), 'Valid form entry is kept');
    }

    public function testPHPfilter_ValidateBoolean()
    {
        $someBoolean = '0';
        $_POST['entry'] = $someBoolean;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('entry')
            ->addFilter('entry', 'boolean');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as valid');
        $this->assertFalse($form->get('entry'), 'Valid boolean entry is converted');
    }

    public function testPHPfilter_Regexp_Ok()
    {
        $someBoolean = 'coco';
        $_POST['entry'] = $someBoolean;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('entry')
            ->addFilter('entry', FILTER_VALIDATE_REGEXP, 'Error', ['regexp'=>'/^[a-zA-Z0-9_]*$/']);
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as valid');
    }

    public function testPHPfilter_Regexp_Nok()
    {
        $someBoolean = 'côcô';
        $_POST['entry'] = $someBoolean;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('entry')
            ->addFilter('entry', FILTER_VALIDATE_REGEXP, 'Error', ['regexp'=>'/^[a-zA-Z0-9_]*$/']);
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
    }


    /*************************************************************************
    EXCEPTION TEST METHODS
     *************************************************************************/
 /*   public function testException_UnknownField()
    {
        $this->setExpectedException( 'Exception' );
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addFilter('login', FILTER_SANITIZE_STRING);
    }

    public function testException_UnknownField_Options()
    {
        $this->setExpectedException( 'Exception' );
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addFilter('username', FILTER_VALIDATE_REGEXP)
            ->setFilterOptions('login', FILTER_VALIDATE_REGEXP, ['regexp'=>'/^[a-zA-Z0-9_]*$/']);
    }

    public function testException_UnknownFilter()
    {
        $this->setExpectedException( 'Exception' );
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->setFilterOptions('username', FILTER_VALIDATE_REGEXP, ['regexp'=>'/^[a-zA-Z0-9_]*$/']);
    }*/
}