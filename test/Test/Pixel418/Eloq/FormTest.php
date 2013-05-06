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

    public function testActiveForm_Clear()
    {
        $_POST['username'] = 'lord.commandant.mormont';
        $form = $this->getLoginForm();
        $form->treat();
        $this->assertEquals($_POST['username'], $form->username, 'Input has the fetch value');
        $form->clear();
        $this->assertNull($form->username, 'Input was cleared');
    }


    /* REQUIRED TEST METHODS
     *************************************************************************/
    public function testRequiredEntry_Null()
    {
        $_POST['username'] = 'balon.greyjoy';
        $form = $this->getLoginForm()
            ->addInputFilters('password', 'required');
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
            ->addInputFilters('password', 'required');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals('', $form->password, 'The password fetched value must be an empty string');
        $this->assertEquals('required', $form->getInputError('password'), 'One error must be thrown, the password field must be required');
        $this->assertFalse($form->isInputValid('password'), 'The password field must be invalid');
    }

    public function testRequiredEntry_Given()
    {
        $_POST['username'] = 'talisa.maegyr';
        $_POST['password'] = 'secret';
        $form = $this->getLoginForm()
            ->addInputFilters('password', 'required');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($_POST['password'], $form->password, 'The password fetched value must be the given string');
        $this->assertNull($form->getInputError('password'), 'No error must be thrown');
        $this->assertTrue($form->isInputValid('password'), 'The password field must be valid');
    }


    /* MAX & MIN LENGTH TEST METHODS
     *************************************************************************/
    public function testMaxLengthEntry_Nok()
    {
        $_POST['username'] = 'margaery.tyrell'; // username length: 15
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'max_length:14');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals('max_length', $form->getInputError('username'), 'One error must be thrown, the username field must be too long');
    }

    public function testMaxLengthEntry_Ok()
    {
        $_POST['username'] = 'olenna.tyrell'; // username length: 13
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'max_length:13');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testMinLengthEntry_Nok()
    {
        $_POST['username'] = 'mance.raider'; // username length: 12
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'min_length:13');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals('min_length', $form->getInputError('username'), 'One error must be thrown, the username field must be too short');
    }

    public function testMinLengthEntry_Ok()
    {
        $_POST['username'] = 'brienne.de.torth'; // username length: 16
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'min_length:16');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }


    /* PHP FILTER TEST METHODS
     *************************************************************************/
    public function testPHPfilter_SanitizeStripTag_AsId()
    {
        $username = 'xaro.xhoan.daxos';
        $_POST['username'] = $username.'<script>';
        $form = $this->getLoginForm()
            ->addInputFilters('username', FILTER_SANITIZE_STRING);
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($username, $form->getInputValue('username'), 'The username input must be sanitized');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testPHPfilter_SanitizeStripTag_AsName()
    {
        $username = 'ygritte';
        $_POST['username'] = '<script>'.$username;
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'string');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($username, $form->getInputValue('username'), 'The username input must be sanitized');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testPHPfilter_ValidateEmail_Nok()
    {
        $_POST['username'] = 'jaqen.h-ghar';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'validate_email');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must not be valid');
        $this->assertEquals($_POST['username'], $form->getInputValue('username'), 'The invalid email must be intact');
        $this->assertEquals('validate_email', $form->getInputError('username'), 'One error must be thrown, the username field must not be a valid email');
    }

    public function testPHPfilter_ValidateEmail_Ok()
    {
        $_POST['username'] = 'craster@freefolk.north';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'validate_email');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($_POST['username'], $form->username, 'The given entry must be intact');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testPHPfilter_ValidateBoolean()
    {
        $_POST['username'] = '0';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'boolean');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertFalse($form->username, 'The given entry must be a false boolean');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testPHPfilter_Regexp_Ok()
    {
        $_POST['username'] = 'davos.mervault';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'validate_regexp:/^[a-zA-Z0-9.]*$/');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($_POST['username'], $form->username, 'The given entry must be intact');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testPHPfilter_Regexp_Nok()
    {
        $_POST['username'] = 'mélisandre d’asshaï';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'validate_regexp:/^[a-zA-Z0-9.]*$/');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals($_POST['username'], $form->username, 'The given entry must be intact');
        $this->assertEquals('validate_regexp', $form->getInputError('username'), 'One error must be thrown, the username field must not respect the regexp');
    }


    /* LIVE FILTER TEST METHODS
     *************************************************************************/
    public function testLiveFildet_Validation_Ok()
    {
        $_POST['username'] = 'alliser.thorne';
        $form = $this->getLoginForm()
            ->addInputFilter('username', 'with_a', function($field){
                return \UString::has($field, 'a');
            });
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($_POST['username'], $form->username, 'The given entry must be intact');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testLiveFildet_Validation_Nok()
    {
        $_POST['username'] = 'syrio.forel';
        $form = $this->getLoginForm()
            ->addInputFilter('username', 'with_a', function($field){
                return \UString::has($field, 'a');
            });
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals($_POST['username'], $form->username, 'The given entry must be intact');
        $this->assertEquals('with_a', $form->getInputError('username'), 'One error must be thrown, the username field must not validate the live filter');
    }


    /* EXCEPTION TEST METHODS
     *************************************************************************/
    public function testException_UnknownField()
    {
        $this->setExpectedException( 'RuntimeException' );
        $form = $this->getLoginForm()
            ->addInputFilters('login', FILTER_SANITIZE_STRING);
    }

    public function testException_UnknownField_Options()
    {
        $this->setExpectedException( 'RuntimeException' );
        $form = $this->getLoginForm()
            ->addInputFilters('username', FILTER_VALIDATE_REGEXP)
            ->setInputFilterOption('login', FILTER_VALIDATE_REGEXP, '/^[a-zA-Z0-9_]*$/');
    }

    public function testException_UnknownFilter()
    {
        $_POST['username'] = 'stannis.baratheon';
        $this->setExpectedException( 'RuntimeException' );
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'php3')
            ->treat();
    }

    public function testException_UnknownFilter_Options()
    {
        $this->setExpectedException( 'RuntimeException' );
        $form = $this->getLoginForm()
            ->addInputFilters('username', FILTER_SANITIZE_STRING)
            ->setInputFilterOption('username', FILTER_VALIDATE_REGEXP, '/^[a-zA-Z0-9_]*$/');
    }

    public function testException_MissingOption()
    {
        $_POST['username'] = 'samwell.tarly';
        $this->setExpectedException( 'RuntimeException' );
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'php')
            ->treat();
    }
}