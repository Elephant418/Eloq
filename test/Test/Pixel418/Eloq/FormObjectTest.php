<?php

namespace Test\Pixel418\Eloq;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Pixel418\Eloq\FormObject as FormObject;
use Pixel418\Eloq\FormInput as FormInput;
use Pixel418\Eloq\FormInputFilter as FormInputFilter;

echo 'Eloq ' . \Pixel418\Eloq::VERSION . ' tested with ';

class FormObjectTest extends \PHPUnit_Framework_TestCase
{

    public function getLoginForm()
    {
        return (new FormObject)
            ->addInput(new FormInput('username'))
            ->addInput(new FormInput('password'));
    }


    /*************************************************************************
    BASIC TEST METHODS
     *************************************************************************/
    public function testNewInstance()
    {
        $form = (new FormObject);
        $this->assertTrue(is_a($form, 'Pixel418\\Eloq\\Stack\\Util\\FormObject'), 'Form must be an object');
    }

    public function testInputSetByName()
    {
        $form = (new FormObject)
            ->addInput('username');
        $this->assertTrue(is_a($form->getInput('username'), 'Pixel418\\Eloq\\Stack\\Util\\FormInput'), 'Input must be an object');
    }

    public function testInputInvalidType()
    {
        $this->setExpectedException( 'Exception' );
        $form = (new FormObject)
            ->addInput(new \ArrayObject());
    }

    public function testEmptyForm()
    {
        $form = $this->getLoginForm();
        $this->assertFalse($form->isActive(), 'Form must inactive');
    }

    public function testInactiveForm()
    {
        $form = $this->getLoginForm()
            ->setPopulation(['unknownEntry'=>'someValue']);
        $this->assertFalse($form->isActive(), 'Form must inactive');
    }

    public function testActiveFullForm()
    {
        $form = $this->getLoginForm()
            ->setPopulation(['username'=>'tzi', 'password'=>'secret']);
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
    }

    public function testActivePartialForm()
    {
        $form = $this->getLoginForm()
            ->setPopulation(['username'=>'roosebolton']);
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
    }


    /*************************************************************************
    FORM INPUT TEST METHODS
     *************************************************************************/
    public function testInactiveForm_NoValues()
    {
        $form = $this->getLoginForm();
        $form->treat();
        $this->assertNull($form->username->getValue(), 'Input has a NULL value');
        $this->assertNull($form->username->getError(), 'Input has a no error');
    }

    public function testInactiveForm_DefaultValues()
    {
        $defaultValue = 'thorosdemyr';
        $form = $this->getLoginForm();
        $form->treat();
        $form->username->setDefaultValue($defaultValue);
        $this->assertEquals($defaultValue, $form->username->getValue(), 'Input has the default value');
        $this->assertNull($form->username->getError(), 'Input has a no error');
    }

    public function testActiveForm_FetchValues()
    {
        $username = 'bericdondarrion';
        $form = $this->getLoginForm()
            ->setPopulation(['username'=>$username]);
        $form->treat();
        $this->assertEquals($username, $form->username->getValue(), 'Input has the fetch value');
        $this->assertEquals($username, (string) $form->username, 'Input could be catch as string');
        $this->assertNull($form->username->getError(), 'Input has a no error');
        $this->assertNull($form->password->getValue(), 'Input has a NULL value');
        $this->assertNull($form->password->getError(), 'Input has a no error');
    }


    /*************************************************************************
    REQUIRED TEST METHODS
     *************************************************************************/
 /*   public function testRequiredEntry_Null()
    {
        $username = 'tzi';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addField('password')
            ->addFilter('password', 'required');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertNull($form->get('password'), 'Non-existing form entry is null');
        $this->assertEquals(1, count($form->getErrors('password')), 'One error message for required entry');
    }

    public function testRequiredEntry_Empty()
    {
        $username = 'tzi';
        $_POST['username'] = $username;
        $_POST['password'] = '';
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addField('password')
            ->addFilter('password', 'required');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals('', $form->get('password'), 'Required form entry is intact');
        $this->assertEquals(1, count($form->getErrors('password')), 'One error message for required entry');
    }

    public function testRequiredEntry_Given()
    {
        $username = 'tzi';
        $password = 'secret';
        $_POST['username'] = $username;
        $_POST['password'] = $password;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addField('password')
            ->addFilter('password', 'required');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as valid');
        $this->assertEquals($password, $form->get('password'), 'Existing required entry');
        $this->assertEquals(array(), $form->getErrors('password'), 'No error message for required entry');
    }


    /*************************************************************************
    MAX & MIN LENGTH TEST METHODS
     *************************************************************************/
 /*   public function testMaxLengthEntry_Nok()
    {
        $username = '1234567890123456';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addFilter('username', 'max_length', 'Too long', array('length' => 15));
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals(1, count($form->getErrors('username')), 'One error message for too long entry');
    }

    public function testMaxLengthEntry_Ok()
    {
        $username = '1234567890123456';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addFilter('username', 'max_length', 'Too long', array('length' => 16));
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals(0, count($form->getErrors('v')), 'No error message for too long entry');
    }

    public function testMinLengthEntry_Nok()
    {
        $username = '1234567890123456';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addFilter('username', 'min_length', 'Too short', array('length' => 17));
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals(1, count($form->getErrors('username')), 'One error message for short long entry');
    }

    public function testMinLengthEntry_Ok()
    {
        $username = '1234567890123456';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->setValues( $_POST )
            ->addField('username')
            ->addFilter('username', 'min_length', 'Too short', array('length' => 16));
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals(0, count($form->getErrors('v')), 'No error message for too short entry');
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