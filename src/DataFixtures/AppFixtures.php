<?php

namespace App\DataFixtures;

use App\Entity\Coupon;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Продукт 1
        $iphone = new Product();
        $iphone->setName('Iphone');
        $iphone->setPrice(100);
        $manager->persist($iphone);

        // Продукт 2
        $headphones = new Product();
        $headphones->setName('Headphones');
        $headphones->setPrice(20);
        $manager->persist($headphones);

        // Продукт 3
        $case = new Product();
        $case->setName('Case');
        $case->setPrice(10);
        $manager->persist($case);

        // Купон 1 (например, 15 евро скидка)
        $couponFixed = new Coupon();
        $couponFixed->setCode('D15');
        $couponFixed->setDiscountType('fixed'); // или Enum, если вы используете
        $couponFixed->setValue(15);
        $manager->persist($couponFixed);

        // Купон 2 (10%)
        $coupon10 = new Coupon();
        $coupon10->setCode('P10');
        $coupon10->setDiscountType('percent');
        $coupon10->setValue(10);
        $manager->persist($coupon10);

        // Купон 3 (100%)
        $coupon100 = new Coupon();
        $coupon100->setCode('P100');
        $coupon100->setDiscountType('percent');
        $coupon100->setValue(100);
        $manager->persist($coupon100);

        // Сохраняем всё в базе
        $manager->flush();
    }
}
