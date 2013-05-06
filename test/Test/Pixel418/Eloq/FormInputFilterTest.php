<?php

namespace Test\Pixel418\Eloq;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Pixel418\Eloq\Stack\Util\Form;

echo 'Eloq ' . \Pixel418\Eloq::VERSION . ' tested with ';

class FormInputFilterTest extends \PHPUnit_Framework_TestCase
{

    public function getLoginForm()
    {
        return (new Form)
            ->addInput('username')
            ->addInput('password')
            // A hack to allow $_POST data simulation
            ->setPopulation($_POST);
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
        $this->assertEquals('This field is required', $form->getInputErrorMessage('password'), 'One explicit error message could be display');
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
        $this->assertNull($form->getInputErrorMessage('password'), 'No error message could be display');
    }


    /* MAX LENGTH TEST METHODS
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

    public function testMaxLengthEntry_Empty()
    {
        $_POST['username'] = ''; // username length: 0
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'max_length:13');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testMaxLengthEntry_Required()
    {
        $_POST['username'] = ''; // username length: 0
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'max_length:14|required');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals('required', $form->getInputError('username'), 'One error must be thrown, the username field is required');
    }


    /* MIN LENGTH TEST METHODS
     *************************************************************************/
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

    public function testMinLengthEntry_Empty()
    {
        $_POST['username'] = ''; // username length: 0
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'min_length:16');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testMinLengthEntry_Required()
    {
        $_POST['username'] = ''; // username length: 0
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'min_length:16|required');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals('required', $form->getInputError('username'), 'One error must be thrown, the username field is required');
    }


    /* CONFIRM TEST METHODS
     *************************************************************************/
    public function testConfirmEntry_Nok()
    {
        $_POST = ['password' => 'secret', 'password2' => 'secr3t'];
        $form = $this->getLoginForm()
            ->addInput('password2', 'confirm:password');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals('confirm', $form->getInputError('password2'), 'One error must be thrown, the password field must not be confirmed');
    }

    public function testConfirmEntry_Ok()
    {
        $_POST = ['password' => 'secr3t', 'password2' => 'secr3t'];
        $form = $this->getLoginForm()
            ->addInput('password2', 'confirm:password');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertNull($form->getInputError('password2'), 'No error must be thrown');
    }


    /* PHP FILTER TEST METHODS
     *************************************************************************/
    public function testPHPFilter_SanitizeStripTag_AsId()
    {
        $username = 'xaro.xhoan.daxos';
        $_POST['username'] = $username . '<script>';
        $form = $this->getLoginForm()
            ->addInputFilters('username', FILTER_SANITIZE_STRING);
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($username, $form->getInputValue('username'), 'The username input must be sanitized');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testPHPFilter_SanitizeStripTag_AsName()
    {
        $username = 'ygritte';
        $_POST['username'] = '<script>' . $username;
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'string');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($username, $form->getInputValue('username'), 'The username input must be sanitized');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }


    /* VALIDATE_EMAIL TEST METHODS
     *************************************************************************/
    public function testPHPFilter_ValidateEmail_Nok()
    {
        $_POST['username'] = 'jaqen.h-ghar';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'validate_email');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must not be valid');
        $this->assertEquals($_POST['username'], $form->getInputValue('username'), 'The invalid email must be intact');
        $this->assertEquals('validate_email', $form->getInputError('username'), 'One error must be thrown, the username field must not be a valid email');
    }

    public function testPHPFilter_ValidateEmail_Ok()
    {
        $_POST['username'] = 'craster@freefolk.north';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'validate_email');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($_POST['username'], $form->username, 'The given entry must be intact');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testPHPFilter_ValidateEmail_Empty()
    {
        $_POST['username'] = '';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'validate_email');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($_POST['username'], $form->username, 'The given entry must be intact');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testPHPFilter_ValidateEmail_Required()
    {
        $_POST['username'] = '';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'validate_email|required');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must not be valid');
        $this->assertEquals($_POST['username'], $form->getInputValue('username'), 'The invalid email must be intact');
        $this->assertEquals('required', $form->getInputError('username'), 'One error must be thrown, the username field is required');
    }


    /* BOOLEAN TEST METHODS
     *************************************************************************/
    public function testPHPFilter_ValidateBoolean_false()
    {
        $_POST['username'] = '0';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'boolean');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertFalse($form->username, 'The given entry must be a false boolean');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testPHPFilter_ValidateBoolean_empty()
    {
        $_POST['username'] = '';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'boolean|required');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertFalse($form->username, 'The given entry must be a false boolean');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }


    /* BOOLEAN TEST METHODS
     *************************************************************************/
    public function testPHPFilter_Regexp_Ok()
    {
        $_POST['username'] = 'davos.mervault';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'validate_regexp:/^[a-zA-Z0-9.]*$/');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($_POST['username'], $form->username, 'The given entry must be intact');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }


    /* REGEXP TEST METHODS
     *************************************************************************/
    public function testPHPFilter_Regexp_Nok()
    {
        $_POST['username'] = 'mélisandre d’asshaï';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'validate_regexp:/^[a-zA-Z0-9.]*$/');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals($_POST['username'], $form->username, 'The given entry must be intact');
        $this->assertEquals('validate_regexp', $form->getInputError('username'), 'One error must be thrown, the username field must not respect the regexp');
    }

    public function testPHPFilter_Regexp_Empty()
    {
        $_POST['username'] = '';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'validate_regexp:/^[a-zA-Z0-9.]*$/');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals($_POST['username'], $form->username, 'The given entry must be intact');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }

    public function testPHPFilter_Regexp_Required()
    {
        $_POST['username'] = '';
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'validate_regexp:/^[a-zA-Z0-9.]*$/|required');
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals($_POST['username'], $form->username, 'The given entry must be intact');
        $this->assertEquals('required', $form->getInputError('username'), 'One error must be thrown, the username field is required');
    }


    /* LIVE FILTER TEST METHODS
     *************************************************************************/
    public function testLiveFildet_Validation_Ok()
    {
        $_POST['username'] = 'alliser.thorne';
        $form = $this->getLoginForm()
            ->addInputFilter('username', 'with_a', function ($field) {
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
            ->addInputFilter('username', 'with_a', function ($field) {
                return ($field === '' || \UString::has($field, 'a'));
            });
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertFalse($form->isValid(), 'Form must be invalid');
        $this->assertEquals($_POST['username'], $form->username, 'The given entry must be intact');
        $this->assertEquals('with_a', $form->getInputError('username'), 'One error must be thrown, the username field must not validate the live filter');
    }

    public function testLiveFildet_Sanitize()
    {
        $_POST['username'] = 'lancel.lannister';
        $form = $this->getLoginForm()
            ->addInputFilter('username', 'without_a', function (&$field) {
                $field = str_replace('a', '', $field);
            });
        $this->assertTrue($form->isActive(), 'Form must be active');
        $this->assertTrue($form->isValid(), 'Form must be valid');
        $this->assertEquals('lncel.lnnister', $form->username, 'The given entry must be intact');
        $this->assertNull($form->getInputError('username'), 'No error must be thrown');
    }
}