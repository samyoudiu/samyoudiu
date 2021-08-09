<?php
//print_invoice.php
if(isset($_GET["pdf"]) && isset($_GET["id"]))
{
 require_once 'pdf.php';
 include('database_connection.php');
 $output = '';
 $statement = $connect->prepare("
  SELECT * FROM tbl_order 
  WHERE order_id = :order_id
  LIMIT 1
 ");
 $statement->execute(
  array(
   ':order_id'       =>  $_GET["id"]
  )
 );
 $result = $statement->fetchAll();
 foreach($result as $row)
 {
  $output .= '
    <style>
        .font-zh {
            font-family: "wt011" 
                    }
</style>
   <table width="100%" border="1" cellpadding="5" cellspacing="0">
    <tr>
     <td colspan="2" align="center" style="font-size:18px"><b>Invoice</b></td>
    </tr>
    <tr>
     <td colspan="2">
      <table width="100%" cellpadding="5">
       <tr>
        <td width="65%"><span class="font-zh">
         客戶姓名 : '.$row["order_receiver_name"].'<br /> 
         工程地點 : '.$row["order_receiver_address"].'<br />
        </td>
        <td width="35%"><span class="font-zh">
         單號 : '.$row["order_no"].'<br />
         日期 : '.$row["order_date"].'<br />
        </td>
       </tr>
      </table>
      <br />
      <table width="100%" border="1" cellpadding="5" cellspacing="0">
       <tr>
        <th><span class="font-zh">項目號</span></th>
        <th><span class="font-zh">項目</span></th>
        <th><span class="font-zh">數量</span></th>
        <th><span class="font-zh">單價</span></th>
        <th><span class="font-zh">合共</span></th>
        <th colspan="2"><span class="font-zh">折扣 (%)</span></th>
        <th colspan="2"><span class="font-zh">折扣 (-)</span></th>
        <th rowspan="2"><span class="font-zh">金額（港元）</span></th>
       </tr>
       <tr>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th>Rate</th>
        <th>Amt.</th>
        <th>Rate</th>
        <th>Amt.</th>
       </tr>';
  $statement = $connect->prepare(
   "SELECT * FROM tbl_order_item 
   WHERE order_id = :order_id"
  );
  $statement->execute(
   array(
    ':order_id'       =>  $_GET["id"]
   )
  );
  $item_result = $statement->fetchAll();
  $count = 0;
  foreach($item_result as $sub_row)
  {
   $count++;
   $output .= '
   <tr>
   <style>
        .font-zh {
            font-family: "wt011" 
                    }
</style>
    <td>'.$count.'</td>
    <td><span class="font-zh">'.$sub_row["item_name"].'</td>
    <td>'.$sub_row["order_item_quantity"].'</td>
    <td>'.$sub_row["order_item_price"].'</td>
    <td>'.$sub_row["order_item_actual_amount"].'</td>
    <td>'.$sub_row["order_item_tax1_rate"].'</td>
    <td>'.$sub_row["order_item_tax1_amount"].'</td>
    <td>'.$sub_row["order_item_tax2_rate"].'</td>
    <td>'.$sub_row["order_item_tax2_amount"].'</td>
    <td>'.$sub_row["order_item_final_amount"].'</td>
   </tr>
   ';
  }
  $output .= '
  <style>
        .font-zh {
            font-family: "wt011" 
                    }
</style>
  <tr>
   <td align="right" colspan="11"><span class="font-zh"><b>金額（港元）</b></td>
   <td align="right"><b>'.$row["order_total_after_tax"].'</b></td>
  </tr>
  ';
  $output .= '
      </table>
     </td>
    </tr>
   </table>
  ';
 }
 $pdf = new Pdf();
 $file_name = 'Invoice-'.$row["order_no"].'.pdf';
 $pdf->loadHtml($output);
 $pdf->render();
 $pdf->stream($file_name, array("Attachment" => false));
}
?>