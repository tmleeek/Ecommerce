<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced SEO Suite
 * @version   1.3.9
 * @build     1298
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_SEO_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{
    /** @var  Mirasvit_SeoAutolink_Helper_Data */
    protected $parseHelper;

    public function setUp()
    {
        parent::setUp();
        $this->parseHelper = Mage::helper('seoautolink');
    }


    /**
     * @test
     * @loadFixture data
     */
    public function globalSubstituationsNumberTest()
    {
        $this->parseHelper = Mage::helper('seoautolink');
        $link1 = new Varien_Object(array(
            'keyword' => 'link1',
            'url' => 'http://link1.com',
            'max_replacements' => 2
        ));

        $initialText = 'text text link1 keyword text';
        $insertedText = "text text <a href='http://link1.com' class='autolink' >link1</a> keyword text";
        $result = $this->parseHelper->_addLinks($initialText, array($link1));
        $this->assertequals($insertedText, $result);

        $result = $this->parseHelper->_addLinks($initialText, array($link1));
        $this->assertequals($insertedText, $result);

        $result = $this->parseHelper->_addLinks($initialText, array($link1));
        $this->assertequals($initialText, $result); //should not add more links
    }

    /**
     * @test
     * @loadFixture data
     */
    public function globalSubstituationsNumberTest2()
    {
        $this->parseHelper = Mage::helper('seoautolink');
        $link1 = new Varien_Object(array(
            'keyword' => 'link2',
            'url' => 'http://link2.com',
            'max_replacements' => 4
        ));

        $initialText = 'text text Link2 link2 keyword text';
        $result = $this->parseHelper->_addLinks($initialText, array($link1));
        $this->assertequals("text text <a href='http://link2.com' class='autolink' >Link2</a> <a href='http://link2.com' class='autolink' >link2</a> keyword text", $result);

        $result = $this->parseHelper->_addLinks($initialText, array($link1));
        $this->assertequals("text text <a href='http://link2.com' class='autolink' >Link2</a> <a href='http://link2.com' class='autolink' >link2</a> keyword text", $result);

        $result = $this->parseHelper->_addLinks($initialText, array($link1));
        $this->assertequals($initialText, $result); //should not add more links
    }


//    /**
//     * @test
//     * @loadFixture data
//     */
//    public function getLinksTest()
//    {
//        $links = $this->parseHelper->getLinks('text text keyword text');
//        $this->assertequals(1, count($links));
//
//        $links = $this->parseHelper->getLinks('text text keyword 2 text');
//        $this->assertequals(2, count($links));
//
//        $links = $this->parseHelper->getLinks('текст текст кейворд текст');
//        $this->assertequals(1, count($links));
//
//        $links = $this->parseHelper->getLinks('текст текст \' " \\ кейворд текст');
//        $this->assertequals(1, count($links));
//    }
//
//    /**
//     * @test
//     */
//    public function isAlnumTest()
//    {
//        $this->assertequals(true, $this->parseHelper->is_alnum('3'));
//        $this->assertequals(true, $this->parseHelper->is_alnum('f'));
//        $this->assertequals(true, $this->parseHelper->is_alnum('ф'));
//        $this->assertequals(true, $this->parseHelper->is_alnum('0'));
//        $this->assertequals(false, $this->parseHelper->is_alnum(' '));
//        $this->assertequals(false, $this->parseHelper->is_alnum('-'));
//        $this->assertequals(8, $this->parseHelper->strlen('спиннинг'));
//        $this->assertequals(8, $this->parseHelper->strlen('spinning'));
//      // $this->assertequals('Лучффффиннинги ультралайт', $this->parseHelper->substr_replace('Лучшие спиннинги ультралайт', 'фффф', 3, 8));
//      $this->assertequals('Лучшие ффффнинги ультралайт', $this->parseHelper->str_replace('спин', 'фффф', 'Лучшие спиннинги ультралайт'));
//        $this->assertequals('Л', $this->parseHelper->get_char('Лучшие', 0));
//        $this->assertequals('ш', $this->parseHelper->get_char('Лучшие', 3));
//        $this->assertequals(false, $this->parseHelper->get_char('Лучшие', 6));
//        $this->assertequals(false, $this->parseHelper->get_char('Link2', -1));
//    }
//
//    /**
//     * @test
//     * @dataProvider parseProvider
//     */
//    public function parseTest($text, $links, $expectedResult, $replacementCount = false)
//    {
//        $result = $this->parseHelper->_addLinks($text, $links, $replacementCount);
//
//        $this->assertequals($expectedResult, $result);
//    }
//
//    public function parseProvider()
//    {
//        $link1 = new Varien_Object(array(
//            'keyword' => 'link1',
//            'url' => 'http://link1.com',
//            ));
//        $link2 = new Varien_Object(array(
//            'keyword' => 'link2',
//            'url' => 'http://link2.com',
//            ));
//        $link3 = new Varien_Object(array(
//            'keyword' => 'link2 link3',
//            'url' => 'http://link3.com',
//            ));
//        $link4 = new Varien_Object(array(
//            'keyword' => 'спиннинг',
//            'url' => 'http://spinning.com',
//            ));
//        $link5 = new Varien_Object(array(
//            'keyword' => 'spinning',
//            'url' => 'http://spinning.com',
//            ));
//        $link6 = new Varien_Object(array(
//            'keyword' => 'solid',
//            'url' => 'http://solid.com',
//            ));
//        $link7 = new Varien_Object(array(
//            'keyword' => 'ในการล็อกอินเพื่อสมัครสมาชิกสามารทำได้2วิธี',
//            'url' => 'http://thai.com',
//            ));
//        $link8 = new Varien_Object(array(
//            'keyword' => '123link',
//            'url' => 'http://123link.com',
//            ));
//
//        return array(
//          array('link1 link2', array($link1, $link2, $link3), "<a href='http://link1.com' class='autolink' >link1</a> <a href='http://link2.com' class='autolink' >link2</a>"),
//          array('link1 link2 link3', array($link1, $link2, $link3), "<a href='http://link1.com' class='autolink' >link1</a> <a href='http://link2.com' class='autolink' >link2</a> link3"),
//          array("<a href='http://link1.com' class='autolink' >link1 aaaa</a>", array($link1, $link3, $link2), "<a href='http://link1.com' class='autolink' >link1 aaaa</a>"),
//          array('link2 link3', array($link3, $link2), "<a href='http://link3.com' class='autolink' >link2 link3</a>"),
//          array('Link2', array($link3, $link2), "<a href='http://link2.com' class='autolink' >Link2</a>"),
//          array('Best spinnings ultra', array($link5), 'Best spinnings ultra'),
//          array('Лучшие спиннинги ультралайт', array($link4), 'Лучшие спиннинги ультралайт'),
//
//          array('link1, Link2', array($link1, $link2), "<a href='http://link1.com' class='autolink' >link1</a>, <a href='http://link2.com' class='autolink' >Link2</a>"),
//          array('link2', array($link2), "<a href='http://link2.com' class='autolink' >link2</a>"),
//          array('link2text', array($link2), 'link2text'),
//          array('textlink2', array($link2), 'textlink2'),
//          array('textlink2text', array($link2), 'textlink2text'),
//          array(',link2,', array($link2), ",<a href='http://link2.com' class='autolink' >link2</a>,"),
//          array(',link2text', array($link2), ',link2text'),
//          array('textlink2,', array($link2), 'textlink2,'),
//          array('link2,', array($link2), "<a href='http://link2.com' class='autolink' >link2</a>,"),
//          array(',link2', array($link2), ",<a href='http://link2.com' class='autolink' >link2</a>"),
//          array('Link2', array($link2), "<a href='http://link2.com' class='autolink' >Link2</a>"),
//          array('Link2text', array($link2), 'Link2text'),
//          array('textLink2', array($link2), 'textLink2'),
//          array('textLink2text', array($link2), 'textLink2text'),
//          array(',Link2,', array($link2), ",<a href='http://link2.com' class='autolink' >Link2</a>,"),
//          array(',Link2text', array($link2), ',Link2text'),
//          array('textLink2,', array($link2), 'textLink2,'),
//          array('Link2,', array($link2), "<a href='http://link2.com' class='autolink' >Link2</a>,"),
//          array(',Link2', array($link2), ",<a href='http://link2.com' class='autolink' >Link2</a>"),
//          array('link1 ‘ ’ “ ” Link2', array($link2), "link1 ‘ ’ “ ” <a href='http://link2.com' class='autolink' >Link2</a>"),
////          array('Pinot Noir, link1 and Pinot Meunier link1', array($link1), "Pinot Noir, <a href='http://link1.com' class='autolink' >link1</a> and Pinot Meunier link1", 1),
//          array('ขั้นตอนการสมัครสมาชิก ในการล็อกอินเพื่อสมัครสมาชิกสามารทำได้2วิธี เมื่อท่านเข้าสู่หน้าโฮมเพจของเราหากท่าน',
//            array($link7),
//            "ขั้นตอนการสมัครสมาชิก <a href='http://thai.com' class='autolink' >ในการล็อกอินเพื่อสมัครสมาชิกสามารทำได้2วิธี</a> เมื่อท่านเข้าสู่หน้าโฮมเพจของเราหากท่าน", ),
//array(
//'With durable solid, wood solidp framing, generous padding and plush stain-resistant microfiber asdsolid. aaaaSolid. upholstery. Solid solid djaslkd asdkjklas ssolid, solid
//solid,
//solid
//Solid.
//Solid',
//array($link6),
//"With durable <a href='http://solid.com' class='autolink' >solid</a>, wood solidp framing, generous padding and plush stain-resistant microfiber asdsolid. aaaaSolid. upholstery. <a href='http://solid.com' class='autolink' >Solid</a> <a href='http://solid.com' class='autolink' >solid</a> djaslkd asdkjklas ssolid, <a href='http://solid.com' class='autolink' >solid</a>
//<a href='http://solid.com' class='autolink' >solid</a>,
//<a href='http://solid.com' class='autolink' >solid</a>
//<a href='http://solid.com' class='autolink' >Solid</a>.
//<a href='http://solid.com' class='autolink' >Solid</a>",
//),
//          array('text 123link text,', array($link8), "text <a href='http://123link.com' class='autolink' > 123link </a> text,"),
//         );
//    }
}
