<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
	<title>֧����ȷ�Ϸ����ӿڽӿ�</title>
</head>
<?php
/* *
 * ���ܣ�ȷ�Ϸ����ӿڽ���ҳ
 * �汾��3.3
 * �޸����ڣ�2012-07-23
 * ˵����
 * ���´���ֻ��Ϊ�˷����̻����Զ��ṩ���������룬�̻����Ը����Լ���վ����Ҫ�����ռ����ĵ���д,����һ��Ҫʹ�øô��롣
 * �ô������ѧϰ���о�֧�����ӿ�ʹ�ã�ֻ���ṩһ���ο���

 *************************ע��*************************
 * ������ڽӿڼ��ɹ������������⣬���԰��������;�������
 * 1���̻��������ģ�https://b.alipay.com/support/helperApply.htm?action=consultationApply�����ύ���뼯��Э�������ǻ���רҵ�ļ�������ʦ������ϵ��Э�����
 * 2���̻��������ģ�http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9��
 * 3��֧������̳��http://club.alipay.com/read-htm-tid-8681712.html��
 * �������ʹ����չ���������չ���ܲ�������ֵ��
 */

require_once("alipay.config.php");
require_once("lib/alipay_submit.class.php");

/**************************�������**************************/

        //֧�������׺�
        $trade_no = $_POST['WIDtrade_no'];
        //����
        //������˾����
        $logistics_name = $_POST['WIDlogistics_name'];
        //����
        //������������
        $invoice_no = $_POST['WIDinvoice_no'];
        //������������
        $transport_type = $_POST['WIDtransport_type'];
        //����ֵ��ѡ��POST��ƽ�ʣ���EXPRESS����ݣ���EMS��EMS��


/************************************************************/

//����Ҫ����Ĳ������飬����Ķ�
$parameter = array(
		"service" => "send_goods_confirm_by_platform",
		"partner" => trim($alipay_config['partner']),
		"trade_no"	=> $trade_no,
		"logistics_name"	=> $logistics_name,
		"invoice_no"	=> $invoice_no,
		"transport_type"	=> $transport_type,
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
);

//��������
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestHttp($parameter);
//����XML
//ע�⣺�ù���PHP5����������֧�֣��迪ͨcurl��SSL��PHP���û��������鱾�ص���ʱʹ��PHP�������
$doc = new DOMDocument();
$doc->loadXML($html_text);

//������������̻���ҵ���߼��������

//�������������ҵ���߼�����д�������´�������ο�������

//��ȡ֧������֪ͨ���ز������ɲο������ĵ���ҳ����תͬ��֪ͨ�����б�

//����XML
if( ! empty($doc->getElementsByTagName( "alipay" )->item(0)->nodeValue) ) {
	$alipay = $doc->getElementsByTagName( "alipay" )->item(0)->nodeValue;
	echo charsetEncode($alipay,'gbk','utf-8');
}

//�������������ҵ���߼�����д�������ϴ�������ο�������

?>
</body>
</html>