<%
/* *
 *���ܣ���׼˫�ӿڽ���ҳ
 *�汾��3.3
 *���ڣ�2012-08-14
 *˵����
 *���´���ֻ��Ϊ�˷����̻����Զ��ṩ���������룬�̻����Ը����Լ���վ����Ҫ�����ռ����ĵ���д,����һ��Ҫʹ�øô��롣
 *�ô������ѧϰ���о�֧�����ӿ�ʹ�ã�ֻ���ṩһ���ο���

 *************************ע��*****************
 *������ڽӿڼ��ɹ������������⣬���԰��������;�������
 *1���̻��������ģ�https://b.alipay.com/support/helperApply.htm?action=consultationApply�����ύ���뼯��Э�������ǻ���רҵ�ļ�������ʦ������ϵ��Э�����
 *2���̻��������ģ�http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9��
 *3��֧������̳��http://club.alipay.com/read-htm-tid-8681712.html��
 *�������ʹ����չ���������չ���ܲ�������ֵ��
 **********************************************
 */
%>
<%@ page language="java" contentType="text/html; charset=gbk" pageEncoding="gbk"%>
<%@ page import="com.alipay.config.*"%>
<%@ page import="com.alipay.util.*"%>
<%@ page import="java.util.HashMap"%>
<%@ page import="java.util.Map"%>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=gbk">
		<title>֧������׼˫�ӿ�</title>
	</head>
	<%
		////////////////////////////////////�������//////////////////////////////////////

		//֧������
		String payment_type = "1";
		//��������޸�
		//�������첽֪ͨҳ��·��
		String notify_url = "http://www.xxx.com/trade_create_by_buyer-JAVA-GBK/notify_url.jsp";
		//��http://��ʽ������·�������ܼ�?id=123�����Զ������
		//ҳ����תͬ��֪ͨҳ��·��
		String return_url = "http://www.xxx.com/trade_create_by_buyer-JAVA-GBK/return_url.jsp";
		//��http://��ʽ������·�������ܼ�?id=123�����Զ������������д��http://localhost/
		//����֧�����ʻ�
		String seller_email = new String(request.getParameter("WIDseller_email").getBytes("ISO-8859-1"),"GBK");
		//����
		//�̻�������
		String out_trade_no = new String(request.getParameter("WIDout_trade_no").getBytes("ISO-8859-1"),"GBK");
		//�̻���վ����ϵͳ��Ψһ�����ţ�����
		//��������
		String subject = new String(request.getParameter("WIDsubject").getBytes("ISO-8859-1"),"GBK");
		//����
		//������
		String price = new String(request.getParameter("WIDprice").getBytes("ISO-8859-1"),"GBK");
		//����
		//��Ʒ����
		String quantity = "1";
		//�������Ĭ��Ϊ1�����ı�ֵ����һ�ν��׿�����һ���¶������ǹ���һ����Ʒ
		//��������
		String logistics_fee = "0.00";
		//������˷�
		//��������
		String logistics_type = "EXPRESS";
		//�������ֵ��ѡ��EXPRESS����ݣ���POST��ƽ�ʣ���EMS��EMS��
		//����֧����ʽ
		String logistics_payment = "SELLER_PAY";
		//�������ֵ��ѡ��SELLER_PAY�����ҳе��˷ѣ���BUYER_PAY����ҳе��˷ѣ�
		//��������
		String body = new String(request.getParameter("WIDbody").getBytes("ISO-8859-1"),"GBK");
		//��Ʒչʾ��ַ
		String show_url = new String(request.getParameter("WIDshow_url").getBytes("ISO-8859-1"),"GBK");
		//����http://��ͷ������·�����磺http://www.xxx.com/myorder.html
		//�ջ�������
		String receive_name = new String(request.getParameter("WIDreceive_name").getBytes("ISO-8859-1"),"GBK");
		//�磺����
		//�ջ��˵�ַ
		String receive_address = new String(request.getParameter("WIDreceive_address").getBytes("ISO-8859-1"),"GBK");
		//�磺XXʡXXX��XXX��XXX·XXXС��XXX��XXX��ԪXXX��
		//�ջ����ʱ�
		String receive_zip = new String(request.getParameter("WIDreceive_zip").getBytes("ISO-8859-1"),"GBK");
		//�磺123456
		//�ջ��˵绰����
		String receive_phone = new String(request.getParameter("WIDreceive_phone").getBytes("ISO-8859-1"),"GBK");
		//�磺0571-88158090
		//�ջ����ֻ�����
		String receive_mobile = new String(request.getParameter("WIDreceive_mobile").getBytes("ISO-8859-1"),"GBK");
		//�磺13312341234
		
		
		//////////////////////////////////////////////////////////////////////////////////
		
		//������������������
		Map<String, String> sParaTemp = new HashMap<String, String>();
		sParaTemp.put("service", "trade_create_by_buyer");
        sParaTemp.put("partner", AlipayConfig.partner);
        sParaTemp.put("_input_charset", AlipayConfig.input_charset);
		sParaTemp.put("payment_type", payment_type);
		sParaTemp.put("notify_url", notify_url);
		sParaTemp.put("return_url", return_url);
		sParaTemp.put("seller_email", seller_email);
		sParaTemp.put("out_trade_no", out_trade_no);
		sParaTemp.put("subject", subject);
		sParaTemp.put("price", price);
		sParaTemp.put("quantity", quantity);
		sParaTemp.put("logistics_fee", logistics_fee);
		sParaTemp.put("logistics_type", logistics_type);
		sParaTemp.put("logistics_payment", logistics_payment);
		sParaTemp.put("body", body);
		sParaTemp.put("show_url", show_url);
		sParaTemp.put("receive_name", receive_name);
		sParaTemp.put("receive_address", receive_address);
		sParaTemp.put("receive_zip", receive_zip);
		sParaTemp.put("receive_phone", receive_phone);
		sParaTemp.put("receive_mobile", receive_mobile);
		
		//��������
		String sHtmlText = AlipaySubmit.buildRequest(sParaTemp,"get","ȷ��");
		out.println(sHtmlText);
	%>
	<body>
	</body>
</html>
