<?php

namespace Test\Pixel418\Eloq;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Pixel418\Eloq\FormHelper as FormHelper;

echo 'Eloq ' . 'v0.1' . ' tested with ';

class FormHelperTest extends \PHPUnit_Framework_TestCase
{

    public function testNewInstance()
    {
        $form = new FormHelper();
        $this->assertTrue(is_a($form, 'Pixel418\\Eloq\\Stack\\Util\\FormHelper'));
    }

    public function testInactiveForm()
    {
        $form = (new FormHelper);
        $form->addField('username')
            ->addField('password');
        $this->assertFalse($form->isActive());
    }

    public function testActiveFullForm()
    {
        $_POST['username'] = 'tzi';
        $_POST['password'] = 'secret';
        $form = (new FormHelper);
        $form->addField('username')
            ->addField('password');
        $this->assertTrue($form->isActive());
    }

    public function testActivePartialForm()
    {
        $username = 'tzi';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->addField('username')
            ->addField('password');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as valid');
        $this->assertEquals($username, $form->getFieldValue('username'), 'Existing form entry is correct');
        $this->assertEquals(array(), $form->getFieldErrors('username'), 'No message for existing entry');
        $this->assertNull($form->getFieldValue('password'), 'Non-existing form entry is null');
        $this->assertEquals(array(), $form->getFieldErrors('password'), 'No message for non-existing entry');
    }


    /*************************************************************************
    REQUIRED TEST METHODS
     *************************************************************************/
    public function testRequiredEntry_Null()
    {
        $username = 'tzi';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->addField('username')
            ->addField('password')
            ->addFilter('password', 'required');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertNull($form->getFieldValue('password'), 'Non-existing form entry is null');
        $this->assertEquals(1, count($form->getFieldErrors('password')), 'One error message for required entry');
    }

    public function testRequiredEntry_Empty()
    {
        $username = 'tzi';
        $_POST['username'] = $username;
        $_POST['password'] = '';
        $form = (new FormHelper);
        $form->addField('username')
            ->addField('password')
            ->addFilter('password', 'required');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals('', $form->getFieldValue('password'), 'Required form entry is intact');
        $this->assertEquals(1, count($form->getFieldErrors('password')), 'One error message for required entry');
    }

    public function testRequiredEntry_Given()
    {
        $username = 'tzi';
        $password = 'secret';
        $_POST['username'] = $username;
        $_POST['password'] = $password;
        $form = (new FormHelper);
        $form->addField('username')
            ->addField('password')
            ->addFilter('password', 'required');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as valid');
        $this->assertEquals($password, $form->getFieldValue('password'), 'Existing required entry');
        $this->assertEquals(array(), $form->getFieldErrors('password'), 'No error message for required entry');
    }


    /*************************************************************************
    PHP FILTER TEST METHODS
     *************************************************************************/
    public function testPHPfilter_SanitizeStripTag_AsId()
    {
        $username = 'tzi<script>';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->addField('username')
            ->addFilter('username', FILTER_SANITIZE_STRING);
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as valid');
        $this->assertEquals('tzi', $form->getFieldValue('username'), 'Sanitize script tag');
    }

    public function testPHPfilter_SanitizeStripTag_AsName()
    {
        $username = 'tzi<script>';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->addField('username')
            ->addFilter('username', 'string');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as valid');
        $this->assertEquals('tzi', $form->getFieldValue('username'), 'Sanitize script tag');
    }

    public function testPHPfilter_ValidateEmail_Nok()
    {
        $username = 'tzi';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->addField('username')
            ->addFilter('username', 'validate_email');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertFalse($form->isValid(), 'Form is detected as invalid');
        $this->assertEquals('tzi', $form->getFieldValue('username'), 'Non-valid email form entry is intact');
        $this->assertEquals(1, count($form->getFieldErrors('username')), 'One error message for non-valid email entry');
    }

    public function testPHPfilter_ValidateEmail_Ok()
    {
        $username = 'tzi@domain.tld';
        $_POST['username'] = $username;
        $form = (new FormHelper);
        $form->addField('username')
            ->addFilter('username', 'validate_email');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as valid');
        $this->assertEquals($username, $form->getFieldValue('username'), 'Valid form entry is kept');
    }

    public function testPHPfilter_ValidateBoolean()
    {
        $someBoolean = '0';
        $_POST['entry'] = $someBoolean;
        $form = (new FormHelper);
        $form->addField('entry')
            ->addFilter('entry', 'boolean');
        $this->assertTrue($form->isActive(), 'Form is detected as active');
        $this->assertTrue($form->isValid(), 'Form is detected as valid');
        $this->assertFalse($form->getFieldValue('entry'), 'Valid boolean entry is converted');
    }
}