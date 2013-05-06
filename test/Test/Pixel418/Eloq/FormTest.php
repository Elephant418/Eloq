<?php

namespace Test\Pixel418\Eloq;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Pixel418\Eloq\Stack\Util\Form;
use Pixel418\Eloq\Stack\Util\FormInputFilter;

echo 'Eloq ' . \Pixel418\Eloq::VERSION . ' tested with ';

class FormTest extends \PHPUnit_Framework_TestCase
{


    /* UTILS
     *************************************************************************/
    private $defaultErrorMessages;

    public function setUp()
    {
        $this->defaultErrorMessages = Form::$defaultErrorMessages;
    }

    public function tearDown()
    {
        Form::$defaultErrorMessages = $this->defaultErrorMessages;
        FormInputFilter::$isInitialized = FALSE;
    }

    public function getLoginForm()
    {
        return (new Form)
            ->addInput('username')
            ->addInput('password')
            // A hack to allow $_POST data simulation
            ->setPopulation($_POST);
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
        $_POST = ['unknownEntry' => 'someValue'];
        $form = $this->getLoginForm();
        $this->assertFalse($form->isActive(), 'Form must be inactive');
    }

    public function testActiveFullForm()
    {
        $_POST = ['username' => 'tzi', 'password' => 'secret'];
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

    public function testActiveForm_WithAddress()
    {
        $_POST = ['prefix' => ['login' => 'beric.dondarrion']];
        $form = $this->getLoginForm()
            ->setInputAddress('username', 'prefix.login')
            ->treat();
        $this->assertEquals($_POST['prefix']['login'], $form->username, 'Input has the fetch value');
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


    /* ERROR MESSAGE TEST METHODS
     *************************************************************************/
    public function testErrorMessage_Default()
    {
        $_POST['username'] = '';
        $form = $this->getLoginForm()
            ->addInputFilter('username', 'filter_without_error_message', function(){
                return FALSE;
            });
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals('filter_without_error_message', $form->getInputError('username'), 'One error must be thrown, the field must be required');
        $this->assertEquals('This field is not valid', $form->getInputErrorMessage('username'), 'The error message is the default one');
    }

    public function testErrorMessage_Filter()
    {
        $_POST['username'] = '';
        $form = $this->getLoginForm()
            ->addInputFilterList('username', 'required');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals('required', $form->getInputError('username'), 'One error must be thrown, the field must be required');
        $this->assertEquals('This field is required', $form->getInputErrorMessage('username'), 'The error message is the default one for the filter');
    }

    public function testErrorMessage_Set_Global()
    {
        $message = 'You must fill in this field';
        Form::defineErrorMessage('required', $message);
        $_POST['username'] = '';
        $form = $this->getLoginForm()
            ->addInputFilterList('username', 'required');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals('required', $form->getInputError('username'), 'One error must be thrown, the field must be required');
        $this->assertEquals($message, $form->getInputErrorMessage('username'), 'The error message is the one set');
    }

    public function testErrorMessage_Set_Form()
    {
        $message = 'You must fill in this field';
        $_POST['username'] = '';
        $form = $this->getLoginForm()
            ->addInputFilterList('username', 'required')
            ->addErrorMessage('required', $message);
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals('required', $form->getInputError('username'), 'One error must be thrown, the field must be required');
        $this->assertEquals($message, $form->getInputErrorMessage('username'), 'The error message is the one set');
    }

    public function testErrorMessage_Set_Filter()
    {
        $message = 'You must fill in this field';
        $_POST['username'] = '';
        $form = $this->getLoginForm()
            ->addInputFilter('username', 'required', $message);
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals('required', $form->getInputError('username'), 'One error must be thrown, the field must be required');
        $this->assertEquals($message, $form->getInputErrorMessage('username'), 'The error message is the one set');
    }

    public function testErrorMessage_Set_FilterList()
    {
        $message = 'Your username must have at least 8 characters';
        $_POST['username'] = '';
        $form = $this->getLoginForm()
            ->addInputFilterList('username', 'required|min_length:8', $message);
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals('required', $form->getInputError('username'), 'One error must be thrown, the field must be required');
        $this->assertEquals($message, $form->getInputErrorMessage('username'), 'The error message is the one set');
    }

    public function testErrorMessage_Variables()
    {
        $message = 'Your username must have at least %s characters';
        $_POST['username'] = 'osha';
        $form = $this->getLoginForm()
            ->addInputFilterList('username', 'min_length:8', $message);
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals('min_length', $form->getInputError('username'), 'One error must be thrown, the field must be required');
        $this->assertEquals('Your username must have at least 8 characters', $form->getInputErrorMessage('username'), 'The error message is the one set');
    }


    /* EXCEPTION TEST METHODS
     *************************************************************************/
    public function testException_UnknownField()
    {
        $this->setExpectedException('RuntimeException');
        $form = $this->getLoginForm()
            ->addInputFilterList('login', FILTER_SANITIZE_STRING);
    }

    public function testException_UnknownField_Options()
    {
        $this->setExpectedException('RuntimeException');
        $form = $this->getLoginForm()
            ->addInputFilterList('username', FILTER_VALIDATE_REGEXP)
            ->setInputFilterOptions('login', FILTER_VALIDATE_REGEXP, '/^[a-zA-Z0-9_]*$/');
    }

    public function testException_UnknownFilter()
    {
        $_POST['username'] = 'stannis.baratheon';
        $this->setExpectedException('RuntimeException');
        $form = $this->getLoginForm()
            ->addInputFilterList('username', 'php3')
            ->treat();
    }

    public function testException_UnknownFilter_Options()
    {
        $this->setExpectedException('RuntimeException');
        $form = $this->getLoginForm()
            ->addInputFilterList('username', FILTER_SANITIZE_STRING)
            ->setInputFilterOptions('username', FILTER_VALIDATE_REGEXP, '/^[a-zA-Z0-9_]*$/');
    }

    public function testException_MissingOption()
    {
        $_POST['username'] = 'samwell.tarly';
        $this->setExpectedException('RuntimeException');
        $form = $this->getLoginForm()
            ->addInputFilterList('username', 'php')
            ->treat();
    }
}