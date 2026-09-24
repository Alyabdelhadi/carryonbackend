<h1 align="center">Package Detail #{{ $data->id }}</h1>


<script type="text/javascript">
function printDiv(divName) {
     var printContents = document.getElementById(divName).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
}
</script>

<div id="printableArea">

<title>Parcel Detail #{{ $data->id }}</title>

<style type="text/css">
td
{
	padding: 10px 10px;
}
</style>

<table width="80%" align="center" border="1" cellpadding="0" cellspacing="0">

<tr>
<td width="50%">
    <b>Package ID</b>
    <p>{{ $data->id }}</p>
</td>

<td width="50%">
    <b>Created On</b>
    <p>{{ date('d-M-Y',strtotime($data->created_at)) }}</p>
    @if($data->order_date)
    <p style="color:red">Needed Before: {{ date('d-M-Y',strtotime($data->order_date)) }}</p>
    @endif
</td>

</tr>

<tr>
<td colspan="4">

<table width="100%" border="0" cellpadding="0" cellspacing="0">

<tr>
<td width="50%"><b>Sender Detail</b>

<p>Name: {{ $data->s_name }}</p>
<p>Phone: {{ $data->s_phone }}</p>
<p>Address Name: {{ $data->s_addressname }}</p>
<p>City: {{ $data->s_city }}, {{ $data->s_country }}</p>
<p>Street: {{ $data->s_street }}</p>
<p>Building: {{ $data->s_building }}</p>
<p>Apartment: {{ $data->s_apartment }}</p>

</td>
<td width="50%"><b>Receiver Detail</b>

<p>Name: {{ $data->r_name }}</p>
<p>Phone: {{ $data->r_phone }}</p>
<p>Address Name: {{ $data->r_addressname }}</p>
<p>City: {{ $data->r_city }}, {{ $data->r_country }}</p>
<p>Street: {{ $data->r_street }}</p>
<p>Building: {{ $data->r_building }}</p>
<p>Apartment: {{ $data->r_apartment }}</p>

</td>
</tr>

</table>

</td>
</tr>
<tr>
<td width="20%"><b>Parcel Type</b></td>
<td width="30%">{{ $cate->name }}</td>
</tr>

<tr>
<td width="20%"><b>Price</b></td>
<td width="30%">${{ $data->price }}</td>
</tr>

<tr>
<td width="20%"><b>Reward</b></td>
<td width="30%">${{ $data->amount }} <small class="text-muted">({{ $data->paymentLabel() }})</small></td>
</tr>



<tr>
<td width="20%"><b>Notes</b></td>
<td width="80%">{{ $data->notes }}</td>
</tr>

</table>
</div>