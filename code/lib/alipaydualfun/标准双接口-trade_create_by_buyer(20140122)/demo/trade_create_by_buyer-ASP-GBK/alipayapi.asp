<%
' ���ܣ���׼˫�ӿڽ���ҳ
' �汾��3.3
' ���ڣ�2012-07-17
' ˵����
' ���´���ֻ��Ϊ�˷����̻����Զ��ṩ���������룬�̻����Ը����Լ���վ����Ҫ�����ռ����ĵ���д,����һ��Ҫʹ�øô��롣
' �ô������ѧϰ���о�֧�����ӿ�ʹ�ã�ֻ���ṩһ���ο���
	
' /////////////////ע��/////////////////
' ������ڽӿڼ��ɹ������������⣬���԰��������;�������
' 1���̻��������ģ�https://b.alipay.com/support/helperApply.htm?action=consultationApply�����ύ���뼯��Э�������ǻ���רҵ�ļ�������ʦ������ϵ��Э�����
' 2���̻��������ģ�http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9��
' 3��֧������̳��http://club.alipay.com/read-htm-tid-8681712.html��
' /////////////////////////////////////

%>
<html>
<head>
	<META http-equiv=Content-Type content="text/html; charset=gb2312">
<title>֧������׼˫�ӿ�</title>
</head>
<body>

<!--#include file="class/alipay_submit.asp"-->

<%
'/////////////////////�������/////////////////////

        '֧������
        payment_type = "1"
        '��������޸�
        '�������첽֪ͨҳ��·��
        notify_url = "http://www.xxx.com/trade_create_by_buyer-ASP-GBK/notify_url.asp"
        '��http://��ʽ������·�������ܼ�?id=123�����Զ������
        'ҳ����תͬ��֪ͨҳ��·��
        return_url = "http://www.xxx.com/trade_create_by_buyer-ASP-GBK/return_url.asp"
        '��http://��ʽ������·�������ܼ�?id=123�����Զ������������д��http://localhost/
        '����֧�����ʻ�
        seller_email = Request.Form("WIDseller_email")
        '����
        '�̻�������
        out_trade_no = Request.Form("WIDout_trade_no")
        '�̻���վ����ϵͳ��Ψһ�����ţ�����
        '��������
        subject = Request.Form("WIDsubject")
        '����
        '������
        price = Request.Form("WIDprice")
        '����
        '��Ʒ����
        quantity = "1"
        '�������Ĭ��Ϊ1�����ı�ֵ����һ�ν��׿�����һ���¶������ǹ���һ����Ʒ
        '��������
        logistics_fee = "0.00"
        '������˷�
        '��������
        logistics_type = "EXPRESS"
        '�������ֵ��ѡ��EXPRESS����ݣ���POST��ƽ�ʣ���EMS��EMS��
        '����֧����ʽ
        logistics_payment = "SELLER_PAY"
        '�������ֵ��ѡ��SELLER_PAY�����ҳе��˷ѣ���BUYER_PAY����ҳе��˷ѣ�
        '��������
        body = Request.Form("WIDbody")
        '��Ʒչʾ��ַ
        show_url = Request.Form("WIDshow_url")
        '����http://��ͷ������·�����磺http://www.xxx.com/myorder.html
        '�ջ�������
        receive_name = Request.Form("WIDreceive_name")
        '�磺����
        '�ջ��˵�ַ
        receive_address = Request.Form("WIDreceive_address")
        '�磺XXʡXXX��XXX��XXX·XXXС��XXX��XXX��ԪXXX��
        '�ջ����ʱ�
        receive_zip = Request.Form("WIDreceive_zip")
        '�磺123456
        '�ջ��˵绰����
        receive_phone = Request.Form("WIDreceive_phone")
        '�磺0571-88158090
        '�ջ����ֻ�����
        receive_mobile = Request.Form("WIDreceive_mobile")
        '�磺13312341234

'/////////////////////�������/////////////////////

'���������������
sParaTemp = Array("service=trade_create_by_buyer","partner="&partner,"_input_charset="&input_charset  ,"payment_type="&payment_type   ,"notify_url="&notify_url   ,"return_url="&return_url   ,"seller_email="&seller_email   ,"out_trade_no="&out_trade_no   ,"subject="&subject   ,"price="&price   ,"quantity="&quantity   ,"logistics_fee="&logistics_fee   ,"logistics_type="&logistics_type   ,"logistics_payment="&logistics_payment   ,"body="&body   ,"show_url="&show_url   ,"receive_name="&receive_name   ,"receive_address="&receive_address   ,"receive_zip="&receive_zip   ,"receive_phone="&receive_phone   ,"receive_mobile="&receive_mobile  )

'��������
Set objSubmit = New AlipaySubmit
sHtml = objSubmit.BuildRequestForm(sParaTemp, "get", "ȷ��")
response.Write sHtml


%>
</body>
</html>
