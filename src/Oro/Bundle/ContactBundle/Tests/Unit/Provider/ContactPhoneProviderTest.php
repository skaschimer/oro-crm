<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Provider;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Provider\ContactPhoneProvider;
use PHPUnit\Framework\TestCase;

class ContactPhoneProviderTest extends TestCase
{
    private ContactPhoneProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->provider = new ContactPhoneProvider();
    }

    public function testGetPhoneNumber(): void
    {
        $entity = new Contact();

        $this->assertNull(
            $this->provider->getPhoneNumber($entity)
        );

        $phone1 = new ContactPhone('123-123');
        $entity->addPhone($phone1);
        $phone2 = new ContactPhone('456-456');
        $phone2->setPrimary(true);
        $entity->addPhone($phone2);

        $this->assertEquals(
            '456-456',
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumbers(): void
    {
        $entity = new Contact();

        $this->assertSame(
            [],
            $this->provider->getPhoneNumbers($entity)
        );

        $phone1 = new ContactPhone('123-123');
        $entity->addPhone($phone1);
        $phone2 = new ContactPhone('456-456');
        $phone2->setPrimary(true);
        $entity->addPhone($phone2);

        $this->assertSame(
            [
                ['123-123', $entity],
                ['456-456', $entity]
            ],
            $this->provider->getPhoneNumbers($entity)
        );
    }
}
