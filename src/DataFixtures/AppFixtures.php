<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Articles
        $apple = new Article()->setName('Apple');
        $manager->persist($apple);

        $lemon = new Article()->setName('Lemon');
        $manager->persist($lemon);

        $orange = new Article()->setName('Orange');
        $manager->persist($orange);

        $kiwi = new Article()->setName('Kiwi');
        $manager->persist($kiwi);

        // Users
        $johnDoe = new User()->setEmail('john.doe@example.com');
        $manager->persist($johnDoe);

        $mrAnderson = new User()->setEmail('mr.anderson@matrix.com');
        $manager->persist($mrAnderson);

        $anonymous = new User()->setEmail('ano@nymo.us');
        $manager->persist($anonymous);

        // Orders
        $orderDoe1 = new Order()
            ->setOwner($johnDoe)
            ->setDate(new \DateTimeImmutable('2021-01-01'))
            ->setStatus(OrderStatus::COMPLETED)
            ->addArticle($apple)
            ->addArticle($lemon);
        $manager->persist($orderDoe1);

        $orderDoe2 = new Order()
            ->setOwner($johnDoe)
            ->setDate(new \DateTimeImmutable('2025-01-01'))
            ->setStatus(OrderStatus::PENDING)
            ->addArticle($kiwi);
        $manager->persist($orderDoe2);

        $orderDoe3 = new Order()
            ->setOwner($johnDoe)
            ->setDate(new \DateTimeImmutable('2024-01-01'))
            ->setStatus(OrderStatus::CANCELLED)
            ->addArticle($lemon)
            ->addArticle($orange);
        $manager->persist($orderDoe3);

        $orderAnderson1 = new Order()
            ->setOwner($mrAnderson)
            ->setDate(new \DateTimeImmutable('2022-01-01'))
            ->setStatus(OrderStatus::COMPLETED)
            ->addArticle($apple)
            ->addArticle($lemon)
            ->addArticle($orange);
        $manager->persist($orderAnderson1);

        $orderAnderson2 = new Order()
            ->setOwner($mrAnderson)
            ->setDate(new \DateTimeImmutable('2023-01-01'))
            ->setStatus(OrderStatus::COMPLETED)
            ->addArticle($kiwi)
            ->addArticle($orange);
        $manager->persist($orderAnderson2);

        $manager->flush();
    }
}
