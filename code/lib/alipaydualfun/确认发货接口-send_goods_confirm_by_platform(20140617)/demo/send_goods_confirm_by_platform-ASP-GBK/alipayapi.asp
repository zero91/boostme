<%
' ���ܣ�ȷ�Ϸ����ӿڽ���ҳ
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
<title>֧����ȷ�Ϸ����ӿ�</title>
</head>
<body>

<!--#include file="class/alipay_submit.asp"-->

<%
'/////////////////////�������/////////////////////

        '֧�������׺�
        trade_no = Request.Form("WIDtrade_no")
        '����
        '������˾����
        logistics_name = Request.Form("WIDlogistics_name")
        '����
        '������������
        invoice_no = Request.Form("WIDinvoice_no")
        '������������
        transport_type = Request.Form("WIDtransport_type")
        '����ֵ��ѡ��POST��ƽ�ʣ���EXPRESS����ݣ���EMS��EMS��

'/////////////////////�������/////////////////////

'���������������
sParaTemp = Array("service=send_goods_confirm_by_platform","partner="&partner,"_input_charset="&input_charset  ,"trade_no="&trade_no   ,"logistics_name="&logistics_name   ,"invoice_no="&invoice_no   ,"transport_type="&transport_type  )

'��������
Set objSubmit = New AlipaySubmit
'������������̻���ҵ���߼��������

'�������������ҵ���߼�����д�������´�������ο�������

'�˴�����������Ҫ��ȡ�Ľڵ㣬��Ѻ���·���Ľڵ������õ������С�
sParaNode = Array("alipay")
'���磺sParaNode = Array("response/tradeBase/trade_no","is_success")

'���ָ���ڵ��ֵ
sParaXml = objSubmit.BuildRequestHttpXml(sParaTemp, sParaNode)

response.Write sParaXml(0)

'�������������ҵ���߼�����д�������ϴ�������ο�������


%>
</body>
</html>
