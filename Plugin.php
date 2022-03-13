<?php
/**
 * 企业微信BOT推送评论通知
 * 
 * @package Qywechat
 * @author Arley
 * @version 1.0.0
 * @link https://arley.fun
 */
class Qywechat_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
    
        Typecho_Plugin::factory('Widget_Feedback')->comment = array('Qywechat_Plugin', 'qy_send');
        Typecho_Plugin::factory('Widget_Feedback')->trackback = array('Qywechat_Plugin', 'qy_send');
        Typecho_Plugin::factory('Widget_XmlRpc')->pingback = array('Qywechat_Plugin', 'qy_send');
        
        return _t('请配置企业微信BOT的 Webhook 地址, 以使您的新评论推送生效');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $key = new Typecho_Widget_Helper_Form_Element_Text('webhook', null, null, _t('Webhook URL'), _t('将企业微信机器人的 Webhook 地址填写到这里'));
        $form->addInput($key->addRule('required', _t('Webhook 不能为空')));
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 企业微信BOT推送
     * 
     * @access public
     * @param array $comment 评论结构
     * @param Typecho_Widget $post 被评论的文章
     * @return void
     */
    public static function qy_send($comment, $post)
    {
        $options = Typecho_Widget::widget('Widget_Options');

        $webhook = $options->plugin('Qywechat')->webhook;
        $postdata = '{"msgtype":"markdown","markdown":{"content":"**有人在您的博客发表了评论**\n>标题：<font color=\"red\">'.$post->title.'</font>\n>评论人：<font color=\"red\">'.$comment['author'].'</font>\n>评论内容：<font color=\"red\">'.$comment['text'].'</font>\n>评论时间：<font color=\"red\">'.date('Y-m-d H:i:s', $comment['created']).'</font>"}';
        
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/json',
                'content' => $postdata
            ],
        ];
        $context  = stream_context_create($opts);
        $result = file_get_contents($webhook, false, $context);
        return $comment;
    }
}
