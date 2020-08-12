<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\DataFixtures\AppFixtures;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Context\RestContext;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class FeatureContext extends RestContext
{
    const USERS = [ 'admin' => '123456aB#' ];
    const AUTH_URL = '/api/login_check';
    const AUTH_JSON = '{ "username": "%s", "password": "%s" }';

    private AppFixtures $fixtures;
    private \Coduo\PHPMatcher\Matcher $matcher;
    private EntityManagerInterface $entityManager;

    public function __construct(\Behatch\HttpCall\Request $request, AppFixtures $fixtures, EntityManagerInterface $entityManager)
    {
        parent::__construct($request);
        $this->fixtures = $fixtures;
        $this->matcher = (new \Coduo\PHPMatcher\Factory\MatcherFactory())->createMatcher();
        $this->entityManager = $entityManager;
    }

    /**
     * @Given I am authenticated as :user
     */
    public function iAmAuthenticatedAsAdmin($user)
    {
        $this->request->setHttpHeader('Content-Type', 'application/ld+json');
        $this->request->send(
            'POST',
            $this->locatePath(self::AUTH_URL),
            [],
            [],
            sprintf(self::AUTH_JSON, $user, self::USERS[$user])
        );

        $json = json_decode($this->request->getContent(), true);

        $this->assertTrue(isset($json['token']));

        $token = $json['token'];

        $this->request->setHttpHeader('Authorization', 'Bearer '.$token);
    }

    /**
     * @Then the JSON matches expected template:
     */
    public function theJsonMatchesExpectedTemplate(PyStringNode $json)
    {
        $actual = $this->request->getContent();

        $matherResult = $this->matcher->match($actual, $json->getRaw());
        echo $this->matcher->getError();
        $this->assertTrue($matherResult);
    }

    /**
     * @BeforeScenario @createSchema
     */
    public function createSchema()
    {
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

        $purger = new ORMPurger($this->entityManager);
        $fixtureExecutor = new ORMExecutor($this->entityManager, $purger);
        $fixtureExecutor->execute([$this->fixtures]);
    }

    /**
     * @BeforeScenario @image
     */
    public function prepareImages()
    {
        copy (__DIR__.'/../../features/fixtures/Stewie.png', __DIR__.'/../../features/fixtures/files/Stewie.png');
    }
}
