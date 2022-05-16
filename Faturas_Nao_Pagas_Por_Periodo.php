<?php

if (!defined("WHMCS"))
	die("Este arquivo não pode ser acessado diretamente");

$reportdata["title"] = "Relatório de Faturas a receber";
$reportdata["description"] = "Este relatório exibe uma lista de faturas não pagas por período e seus respectivos serviços.";

$reportdata["headertext"] = "<form method=\"post\" action=\"$PHP_SELF?report=$report&calculate=true\"><center>Data Inicial: <select name=\"startday\">";
for ( $counter = 1; $counter <= 31; $counter += 1) {
	$reportdata["headertext"] .= "<option";
	if ($counter==$startday) { $reportdata["headertext"] .= " selected"; }
	$reportdata["headertext"] .= ">$counter";
}
$reportdata["headertext"] .= "</select> <select name=\"startmonth\">";
for ( $counter = 1; $counter <= 12; $counter += 1) {
	$reportdata["headertext"] .= "<option";
	if ($counter==$startmonth) { $reportdata["headertext"] .= " selected"; }
	$reportdata["headertext"] .= ">$counter";
}
$reportdata["headertext"] .= "</select> <select name=\"startyear\">";
for ( $counter = 1998; $counter <= 2024; $counter += 1) {
	$reportdata["headertext"] .= "<option";
	if ($counter==$startyear) { $reportdata["headertext"] .= " selected"; }
	$reportdata["headertext"] .= ">$counter";
}
$reportdata["headertext"] .= "</select> End Date: <select name=\"endday\">";
for ( $counter = 1; $counter <= 31; $counter += 1) {
	$reportdata["headertext"] .= "<option";
	if ($counter==$endday) { $reportdata["headertext"] .= " selected"; }
	$reportdata["headertext"] .= ">$counter";
}
$reportdata["headertext"] .= "</select> <select name=\"endmonth\">";
for ( $counter = 1; $counter <= 12; $counter += 1) {
	$reportdata["headertext"] .= "<option";
	if ($counter==$endmonth) { $reportdata["headertext"] .= " selected"; }
	$reportdata["headertext"] .= ">$counter";
}
$reportdata["headertext"] .= "</select> <select name=\"endyear\">";
for ( $counter = 1998; $counter <= 2024; $counter += 1) {
	$reportdata["headertext"] .= "<option";
	if ($counter==$endyear) { $reportdata["headertext"] .= " selected"; }
	$reportdata["headertext"] .= ">$counter";
}
$reportdata["headertext"] .= "</select> Tipo Item: <select name='filtertype'><option value='Todos'<option>Todos</option><option value=''<option></option><option value='Revendas'<option>Revendas</option><option value='PromoHosting'<option>Promoção Host</option><option value='Domain'<option>Dominio</option><option value='LateFee'<option>Multa</option><option value='Hosting'<option>Hospedagem</option>";
$reportopt["headertext"] = "<select name=\"teste\"><option=\"Gerar Relatório\"></select>";
$reportdata["headertext"] .= "</select> <input type=\"submit\" value=\"Gerar Relatório\"></form>"; 

if ($calculate) {

	$startday = str_pad($startday,2,"0",STR_PAD_LEFT);
	$startmonth = str_pad($startmonth,2,"0",STR_PAD_LEFT);
	$endday = str_pad($endday,2,"0",STR_PAD_LEFT);
	$endmonth = str_pad($endmonth,2,"0",STR_PAD_LEFT);

	$startdate = $startyear.$startmonth.$startday;
	$enddate = $endyear.$endmonth.$endday."235959";

	if ($filtertype == "Todos") {
	$query = "SELECT count(*),tblinvoiceitems.id, tblinvoiceitems.invoiceid, tblinvoiceitems.type, tblinvoiceitems.description, tblinvoices.status,  sum(tblinvoiceitems.amount), tblinvoices.id, tblinvoiceitems.paymentmethod, tblinvoices.datepaid FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoiceitems.invoiceid=tblinvoices.id WHERE tblinvoices.status = 'Unpaid' AND datepaid>='$startdate' AND datepaid<='$enddate'";
		}
	
	elseif ($filtertype == "Revendas") {
	$query = "SELECT count(*), tblinvoiceitems.id, tblinvoiceitems.invoiceid, tblinvoiceitems.type, tblinvoiceitems.description, tblinvoices.status,  sum(tblinvoiceitems.amount), tblinvoices.id, tblinvoiceitems.paymentmethod, tblinvoices.datepaid FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoiceitems.invoiceid=tblinvoices.id WHERE tblinvoiceitems.description like 'Revenda%' AND tblinvoices.status = 'Unpaid'  AND datepaid >= '$startdate' AND datepaid <= '$enddate' ";
	}
	else {
	$query = "SELECT count(*), tblinvoiceitems.id, tblinvoiceitems.invoiceid, tblinvoiceitems.type, tblinvoiceitems.description, tblinvoices.status,  sum(tblinvoiceitems.amount), tblinvoices.id, tblinvoiceitems.paymentmethod, tblinvoices.datepaid FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoiceitems.invoiceid=tblinvoices.id WHERE tblinvoices.status = 'Unpaid' AND type = '$filtertype' AND datepaid >= '$startdate' AND datepaid <= '$enddate'";
	}
	
	$result = mysql_query($query);
	$data = mysql_fetch_array($result);
	$numinvoices = $data[0];
	$total = $data[6];
	$tax = $data[99];
    $tax2 = $data[99];
	
	if (!$total) { $total="0.00"; }
	if (!$tax) { $tax="0.00"; }
    if (!$tax2) { $tax2="0.00"; }

	$reportdata["headertext"] .= "<br>$numinvoices Faturas encontradas por $filtertype<br><B>Total a Receber:</B> ".$CONFIG["CurrencySymbol"]."$total &nbsp; <B>Tax Level 1 Liability:</B> ".$CONFIG["CurrencySymbol"]."$tax &nbsp; <B>Tax Level 2 Liability:</B> ".$CONFIG["CurrencySymbol"]."$tax2";
}
$reportdata["headertext"] .= "</center>";

$reportdata["tableheadings"] = array("ID","Fatura", "Tipo","Descricao Item faturado","Total","Forma de pagamento", "Data/hora de Pagamento");

if ($filtertype == "Todos") {
	$query = "SELECT tblinvoiceitems.id, tblinvoiceitems.invoiceid, tblinvoiceitems.type, tblinvoiceitems.description, tblinvoices.status,  tblinvoiceitems.amount, tblinvoices.id, tblinvoiceitems.paymentmethod, tblinvoices.datepaid FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoiceitems.invoiceid=tblinvoices.id WHERE tblinvoices.status = 'Unpaid' AND datepaid>='$startdate' AND datepaid<='$enddate'";
}
elseif ($filtertype == "Revendas") {
$query = "SELECT tblinvoiceitems.id, tblinvoiceitems.invoiceid, tblinvoiceitems.type, tblinvoiceitems.description, tblinvoices.status,  tblinvoiceitems.amount, tblinvoices.id, tblinvoiceitems.paymentmethod, tblinvoices.datepaid FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoiceitems.invoiceid=tblinvoices.id WHERE tblinvoiceitems.description like 'Revenda%' AND tblinvoices.status = 'Unpaid' AND datepaid>='$startdate' AND datepaid<='$enddate' ";
}
else {
	$query = "SELECT tblinvoiceitems.id, tblinvoiceitems.invoiceid, tblinvoiceitems.type, tblinvoiceitems.description, tblinvoices.status,  tblinvoiceitems.amount, tblinvoices.id, tblinvoiceitems.paymentmethod, tblinvoices.datepaid FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoiceitems.invoiceid=tblinvoices.id WHERE tblinvoices.status = 'Unpaid' AND type = '$filtertype' AND datepaid >= '$startdate' AND datepaid <= '$enddate'";
}

$result = mysql_query($query);
while ($data = mysql_fetch_array($result)){
$id = $data["0"];
$tipo = $data["type"];
$descricao = $data["description"];
$invoicenum = $data["invoiceid"];
$linkfatura = "<a href='invoices.php?action=edit&id=$invoicenum'>#$invoicenum</a>";
$total = $CONFIG["CurrencySymbol"].$data["amount"];
$totalformatado = formatCurrency($total); 
$valor = $CONFIG["CurrencySymbol"].$data["amount"];
$dt_pagamento = $data["datepaid"];
$forma_pagamento = $data["paymentmethod"];
$reportdata["tablevalues"][] = array("$id","$linkfatura","$tipo","$descricao",formatCurrency($total),"$forma_pagamento", "$dt_pagamento");
}


# COMMENT the line to remove the branding please do not delete it.
# Would be appreciated if it could be left.
$data["footertext"]="<i>Relatório criado por <a href='http://www.cw2hospedagem.com'>Wellington - Cw2 Hospedagem</a>.";

?>
