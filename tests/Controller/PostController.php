<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;
use App\Repository\SubjectRepository;
use App\Repository\TestRepository;
use Symfony\Component\DomCrawler\Crawler;

class PostControllerTest extends WebTestCase
{
    public function testShowHomePage()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRegister()
    {
        $client = static::createClient();

        // crawl the page
        $crawler = $client->request('GET', '/register');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // find the form
    	$form = $crawler->selectButton('Register')->form();

        // fill in the form
        $form['registration_form[username]']->setValue('lucy');
        $form['registration_form[email]']->setValue('lucy@example.com');
        $form['registration_form[first_name]']->setValue('lucy');
        $form['registration_form[last_name]']->setValue('johnson');
        $form['registration_form[plainPassword]']->setValue('lucy123');
        $form['registration_form[agreeTerms]']->setValue(1);

        // submit the form
    	$client->submit($form);
        $client->followRedirect();
    	$this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testVisitingWhileLoggedIn()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('lucy@example.com');

        // simulate $testUser being logged in
        $client->loginUser($testUser);


        // test e.g. the profile page
        $client->request('GET', '/profile/'.$testUser->getUsername());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $testUser->getUsername());
    }

    public function testAddSubject()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('john.doe@example.com');

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        // crawl the page
        $crawler = $client->request('GET', '/admin/subject/add');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // find the form
    	$form = $crawler->selectButton('Create')->form();

        // fill in the form
        $form['form[name]']->setValue('Test Subject');

        // submit the form
    	$client->submit($form);
    	$this->assertEquals(200, $client->getResponse()->getStatusCode());

        // get added subject
        $subjectRepository = static::$container->get(SubjectRepository::class);
        $testSubject = $subjectRepository->findOneByName('Test Subject');

        // find route and check if the page exists
        $client->request('GET', '/subject/'.$testSubject->getSlug());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
