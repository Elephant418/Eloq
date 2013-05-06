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


    /* EXCEPTION TEST METHODS
     *************************************************************************/
    public function testException_UnknownField()
    {
        $this->setExpectedException('RuntimeException');
        $form = $this->getLoginForm()
            ->addInputFilters('login', FILTER_SANITIZE_STRING);
    }

    public function testException_UnknownField_Options()
    {
        $this->setExpectedException('RuntimeException');
        $form = $this->getLoginForm()
            ->addInputFilters('username', FILTER_VALIDATE_REGEXP)
            ->setInputFilterOptions('login', FILTER_VALIDATE_REGEXP, '/^[a-zA-Z0-9_]*$/');
    }

    public function testException_UnknownFilter()
    {
        $_POST['username'] = 'stannis.baratheon';
        $this->setExpectedException('RuntimeException');
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'php3')
            ->treat();
    }

    public function testException_UnknownFilter_Options()
    {
        $this->setExpectedException('RuntimeException');
        $form = $this->getLoginForm()
            ->addInputFilters('username', FILTER_SANITIZE_STRING)
            ->setInputFilterOptions('username', FILTER_VALIDATE_REGEXP, '/^[a-zA-Z0-9_]*$/');
    }

    public function testException_MissingOption()
    {
        $_POST['username'] = 'samwell.tarly';
        $this->setExpectedException('RuntimeException');
        $form = $this->getLoginForm()
            ->addInputFilters('username', 'php')
            ->treat();
    }
}