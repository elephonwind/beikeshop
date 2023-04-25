<?php
namespace Tests\Browser;
namespace App\Http\Controllers;


use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Facebook\WebDriver\WebDriverBy;
use App\Http\Controllers\By;
use function PHPUnit\Framework\assertNotEquals;

require_once(dirname(__FILE__) . '/../../data/catalog/login.php');
require_once(dirname(__FILE__) . '/../../data/catalog/login_page.php');
require_once(dirname(__FILE__) . '/../../data/catalog/account_page.php');
require_once(dirname(__FILE__) . '/../../data/catalog/product_1.php');
require_once(dirname(__FILE__) . '/../../data/catalog/index_page.php');
require_once(dirname(__FILE__) . '/../../data/catalog/checkout_page.php');
require_once(dirname(__FILE__) . '/../../data/catalog/order_page.php');
require_once(dirname(__FILE__) . '/../../data/admin/login.php');
require_once(dirname(__FILE__) . '/../../data/admin/login_page.php');
require_once(dirname(__FILE__) . '/../../data/admin/order_page.php');
require_once(dirname(__FILE__) . '/../../data/admin/admin_page.php');
require_once(dirname(__FILE__) . '/../../data/admin/express.php');

//已注册客户且有地址，直接购买商品
class AlterOrderStationTest extends DuskTestCase
{
    public function testAlterOrderStation()
    {
        $this->browse(function (Browser $browser)
        {
            $browser->visit(admin_login['login_url'])
            //登录后台
                ->type(admin_login['login_email'], admin_true_login['email'])
                ->type(admin_login['login_pwd'], admin_true_login['password'])
                ->press(admin_login['login_btn'])
                ->pause(2000)
                //去往前台
                ->clicklink(admin_top['root'])
                ->pause(3000)
                ->clickLink(admin_top['go_catalog'])
                ->pause(2000)
                //切换到前台下单
                ->driver->switchTo()->window($browser->driver->getWindowHandles()[1]);
            //前台用户登录
                //点击登录图标
                $browser->click(index_login['login_icon'])
                ->type(login['login_email'], true_login['email'])
                ->type(login['login_pwd'], true_login['password'])
                ->press(login['login_btn'])
                ->pause(5000)
                ->clickLink(account['go_index'])
                //3.向下滑动页面直到找到商品
                ->pause(2000)
                ->scrollIntoView(index['product_img'])
                ->pause(2000)
                //点击要购买的商品
                ->press(index['product_img'])
                //4.点击购买按钮
                ->press(product['product_1'])
                ->pause(5000)
                //5.点击确认按钮
                ->press(checkout['submit'])
                ->pause(5000);
                $elements = $browser->elements(checkout['order_num']);
                $order_num =$elements[15]->getText();
                //打印订单号
                echo $order_num;
                $browser->clickLink(checkout['view_order'])
            //进入后台,修改订单状态为已支付
                ->driver->switchTo()->window($browser->driver->getWindowHandles()[0]);
                //点击订单管理按钮
                $browser->click(admin_top['mg_order'])
                //搜索框输入刚下单的订单号
                ->type(order_right['search_order'],$order_num)
                //点击搜索按钮
                ->press(order_right['search_bth'])
                ->assertSee($order_num)
                //点击查看按钮
                ->press(order_right['view_btn'])
                //点击状态栏下拉按钮
                ->pause(2000)
                ->press(order_details['pull_btn'])
                //修改状态为已支付
                ->pause(2000)
                ->click(order_details['paid'])
                ->press(order_details['alter_btn'])
                ->pause(3000)
            //切换到前台
                ->driver->switchTo()->window($browser->driver->getWindowHandles()[1]);
                $browser->pause(3000)
                //刷新页面
                ->refresh()
                ->pause(5000);
                // 断言是否已支付
                 $text = $browser->text(get_order_status['status_text']);
                $browser->assertSeeIn($text,ca_order_status['paid'])
            //切换到后台，将状态改为已发货
                ->driver->switchTo()->window($browser->driver->getWindowHandles()[0]);
                $browser->pause(2000)
                ->press(order_details['pull_btn'])
                //修改状态为已支付
                ->pause(2000)
                ->click(order_details['paid'])
                ->press(order_details['express_btn'])
                //选择快递并填写订单号
                ->pause(2000);
                // 找到所有 class 为 el-scrollbar__view el-select-dropdown__list 的元素
                $elements = $browser->elements(order_details['express_1']);
                // 获取第二个元素
                $secondElement = $elements[1];
                // 找到第一个子元素并点击它
                $secondElement->findElement(WebDriverBy::xpath('./*[1]'))->click();
                $browser->type(order_details['order_number'], express['express_code'])
                ->press(order_details['submit'])
                ->pause(3000)
            //切换到前台,断言是否已发货
                ->driver->switchTo()->window($browser->driver->getWindowHandles()[1]);
                $browser->pause(3000)
                ->refresh()
                ->pause(4000);
                $text = $browser->text(get_order_status['status_text']);
                $browser->assertSeeIn($text,ca_order_status['Shipped'])
                    ;



        });
    }
}