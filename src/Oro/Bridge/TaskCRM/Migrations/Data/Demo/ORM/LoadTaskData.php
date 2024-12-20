<?php

namespace Oro\Bridge\TaskCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadAccountData;
use Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData;
use Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TaskBundle\Entity\Task;
use Oro\Bundle\TaskBundle\Entity\TaskPriority;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Loads new Task entities.
 */
class LoadTaskData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    private const FIXTURES_COUNT = 20;

    private static array $fixtureSubjects = [
        'Lorem ipsum dolor sit amet, consectetuer adipiscing elit',
        'Aenean commodo ligula eget dolor',
        'Aenean massa',
        'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus',
        'Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem',
        'Nulla consequat massa quis enim',
        'Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu',
        'In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo',
        'Nullam dictum felis eu pede mollis pretium',
        'Integer tincidunt',
        'Cras dapibus',
        'Vivamus elementum semper nisi',
        'Aenean vulputate eleifend tellus',
        'Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim',
        'Aliquam lorem ante, dapibus in, viverra quis, feugiat',
        'Aenean imperdiet. Etiam ultricies nisi vel',
        'Praesent adipiscing',
        'Integer ante arcu',
        'Curabitur ligula sapien',
        'Donec posuere vulputate'
    ];

    private static array $fixtureDescriptions = [
        'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.',
        'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.',
        'Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim.',
        'Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet.',
        'Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi..',
        'Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra,',
        'Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel.',
        'Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus.',
        'Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed.',
        'Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus.',
        'Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus.',
        'Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales.',
        'Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus.',
        'Cras ultricies mi eu turpis hendrerit fringilla. Vestibulum ante ipsum primis in faucibus orci luctus.',
        'Nam pretium turpis et arcu. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum.',
        'Integer ante arcu, accumsan a, consectetuer eget, posuere ut, mauris. Praesent adipiscing.',
        'Vestibulum volutpat pretium libero. Cras id dui. Aenean ut eros et nisl sagittis vestibulum.',
        'Sed lectus. Donec mollis hendrerit risus. Phasellus nec sem in justo pellentesque facilisis. Etiam imperdiet.',
        'Phasellus leo dolor, tempus non, auctor et, hendrerit quis, nisi. Curabitur ligula sapien, tincidunt non.',
        'Praesent congue erat at massa. Sed cursus turpis vitae tortor. Donec posuere vulputate arcu.',
    ];

    #[\Override]
    public function getDependencies(): array
    {
        return [
            LoadContactData::class,
            LoadAccountData::class,
            LoadUsersData::class
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $this->persistDemoTasks($manager, $tokenStorage);
        $manager->flush();
        $tokenStorage->setToken(null);
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function persistDemoTasks(ObjectManager $manager, TokenStorageInterface $tokenStorage): void
    {
        $organization = $this->getReference('default_organization');

        $priorities = $manager->getRepository(TaskPriority::class)->findAll();
        if (empty($priorities)) {
            return;
        }
        $users = $manager->getRepository(User::class)->findBy(['organization' => $organization]);
        if (empty($users)) {
            return;
        }

        $accounts = $manager->getRepository(Account::class)->findAll();
        $contacts = $manager->getRepository(Contact::class)->findAll();
        $status = $manager->getRepository(EnumOption::class)
            ->find(ExtendHelper::buildEnumOptionId('task_status', 'open'));

        for ($i = 0; $i < self::FIXTURES_COUNT; ++$i) {
            /** @var User $assignedTo */
            $assignedTo = $this->getRandomEntity($users);
            /** @var TaskPriority $taskPriority */
            $taskPriority = $this->getRandomEntity($priorities);

            if ($manager->getRepository(Task::class)->findOneBySubject(self::$fixtureSubjects[$i])) {
                // Task with this title is already exist
                continue;
            }

            $task = new Task();
            $task->setSubject(self::$fixtureSubjects[$i]);
            $task->setDescription(self::$fixtureDescriptions[$i]);
            $dueDate = new \DateTime();
            $dueDate->add(new \DateInterval(sprintf('P%dDT%dM', rand(0, 30), rand(0, 1440))));
            $task->setDueDate($dueDate);
            $task->setOwner($assignedTo);
            $task->setCreatedBy($assignedTo);
            $task->setTaskPriority($taskPriority);
            $task->setOrganization($organization);
            $task->setStatus($status);

            $randomPath = rand(1, 10);

            if ($randomPath > 2) {
                $contact = $this->getRandomEntity($contacts);
                if ($contact) {
                    $this->addActivityTarget($task, $contact, $tokenStorage);
                }
            }

            if ($randomPath > 3) {
                $account = $this->getRandomEntity($accounts);
                if ($account) {
                    $this->addActivityTarget($task, $account, $tokenStorage);
                }
            }

            if ($randomPath > 4) {
                $user = $this->getRandomEntity($users);
                if ($user) {
                    $this->addActivityTarget($task, $user, $tokenStorage);
                }
            }

            $manager->persist($task);
        }
    }

    private function getRandomEntity(array $entities): ?object
    {
        if (empty($entities)) {
            return null;
        }

        return $entities[rand(0, \count($entities) - 1)];
    }

    private function addActivityTarget(Task $task, object $target, TokenStorageInterface $tokenStorage): void
    {
        if ($task->supportActivityTarget(\get_class($target))) {
            $user = $task->getOwner();
            $tokenStorage->setToken(new UsernamePasswordOrganizationToken(
                $user,
                'main',
                $this->getReference('default_organization'),
                $user->getUserRoles()
            ));
            $task->addActivityTarget($target);
        }
    }
}
