<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UrlSeederTest extends TestCase
{
    /**
     * These fields must to be present in each JSON files.
     * 
     * @param void
     * 
     * @return void
     */
    public function testFields(): void
    {
        $data = \App\Helpers\Utils::arr2obj(\Database\Seeders\DatabaseSeeder::FIELDS);

        foreach ($data as $url) {
            $content = \App\Helpers\Utils::getSeederJSON($url->{\Database\Seeders\DatabaseSeeder::URL});

            $first = $content->{array_key_first((array)$content)};

            $this->assertTrue(\App\Helpers\Utils::ArrayContains(
                (array) $url->{\Database\Seeders\DatabaseSeeder::COLUMNS},
                (array) array_keys(get_object_vars($first))
            ));
        }
    }

    /**
     * @test
     * 
     * Check if all classes exists.
     * 
     * @param void
     */
    public function assertPreConditions(): void
    {
        foreach (\Database\Seeders\DatabaseSeeder::data() as $data) {
            if ((bool)(class_exists($data)) === false) {
                $this->assertTrue(false);
            }
        }

        $this->assertTrue(true);
    }

    /**
     * URL Test.
     *
     * @return void
     */
    public function testValidUrl(): void
    {
        foreach ($this->URL() as $URL) {
            if ((bool)(\App\Helpers\Utils::getSeederJSON($URL) instanceof \stdClass) === false) {
                $this->assertTrue(false);
            }
        }

        $this->assertTrue(true);
    }

    /**
     * URL Getter.
     * 
     * @param void
     */
    public function URL(): array
    {
        $Address = [];

        foreach (\Database\Seeders\DatabaseSeeder::FIELDS as $KEY => $URL) {
            $Address[] = $URL[\Database\Seeders\DatabaseSeeder::URL];
        }

        return $Address;
    }
}
