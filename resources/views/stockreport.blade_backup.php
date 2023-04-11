<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>stockreport</title>
</head>
<style>
    .m-0{
        margin: 0px;
    }
    .p-0{
        padding: 0px;
    }
    .red{
        color: red;
    }
    .green{
        color: green;
    }
    .brown{
        color: brown;
    }
    .width-100{
        width: 100%;
    }     
    .w-10{
        width:10%;   
    }    
    .w-30{
        width:30%;   
    } 
    .w-50{
        width:50%;   
    }
    .w-70{
        width:70%;   
    }
    .w-90{
        width:90%;   
    } 
    .mt-10{
        margin-top:10px;
    }
    .text-center {
        text-align: center;
    }
    .text-left {
        text-align: left;
    }

    .text-right {
        text-align: right;
    } 
    .text-justify {
        text-align: justify;
    }
    .table-body{
        background-color: rgba(107, 114, 128, 0.3)
    }
    .table-header{
        background-color: rgba(107, 114, 128, 0.5);
    }
    .tableCustom {
        max-width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
        border-collapse: collapse;
        box-sizing: border-box;
        text-indent: initial;
        border-color: gray;
    }
    .tableCustom thead, .box-text {
        background-color: rgba(107, 114, 128, 0.1);
    }
    .tableCustom thead th{
        padding: 4px 4px;
    }
    .p-x{
        padding-left: 10px;
        padding-right: 10px;
    }
    .head-title{
        margin-bottom: 20px;
    }
    .capitalize {
        text-transform: capitalize;
    }
    .uppercase {
        text-transform: uppercase;
    }
    .font-bold {
        font-weight: 700;
    }
    .text-xl {
        font-size: 1.25rem;
        line-height: 1.75rem;
    }
    .text-xs {
        font-size: 0.75rem;
        line-height: 1rem;
    }
    .flex-col {
        flex-direction: column;
    }
    .w-full {
        width: 100%;
    }
    .flex {
        display: flex;
    }
    .top {
    vertical-align: top;
    }

    .details:nth-child(even) {
       /* background: #CCC */
    }
    .details:nth-child(odd) {
       /* background: rgb(190, 186, 186) */
    }

    .details {
        /*border: 1px solid black;*/
    }
    
    table {
    border-collapse: collapse;
    }
</style>
<body>
    <div class="head-title">
        <h1 class="text-center m-0 p-0">Stock Report</h1>
        <h1 class=" text-center capitalize font-bold text-xl">
            macsedo Resort hotel
        </h1>
        <div class="text-center  w-full flex flex-col">
            <span class=" text-xs">Kabuusu,Masaka Road</span>
            <span class=" text-xs">P.O. Box 73369, Kampala, +256 Uganda</span>
            <span class=" text-xs">Phone/Fax:  +256-414272675 / +256-776650268 </span>
            <span class=" text-xs">Email:  info@macsedoresorthotel.com</span>
        </div>
    </div>

    <div class="width-100">
        <table class="width-100">
            <thead>
                <tr class="width-100 table-header">
                    <th class="w-100 text-left ">STOCK DETAILS DATE:  {{ \Carbon\Carbon::parse($Date)->format('l/F/Y') }}</th>
                </tr>
            </thead>
            <tbody class="w-100">
                @foreach ($stock ?? '' as $key=>$value ) 
                <tr class="details width-100">
                    <td class="text-left w-100">
                        <div class="text-left w-full top table-header"  style="font-weight: bold;">{{$key}}</div> {{--section grouped--}}
                        @foreach ($value ?? '' as $key_=>$value_ )
                           <table class="width-100">
                                <thead>
                                    <tr class="width-100">
                                        <th class="text-left w-30">Category</th>
                                        <th class="text-left w-70">Sub-Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="width-100">
                                        <td class="text-left w-30 top">{{ strtolower($key_) }}</td> {{--Category grouped--}}
                                        <td class="text-left w-70">
                                            @foreach ($value_ ?? '' as $value__ ) {{--array of objects--}}
                                            <table class="width-100">
                                                 <thead>
                                                     {{-- <tr class="width-100 table-header">
                                                         <th class="text-left w-50">Stock</th>
                                                         <th class="text-left w-50">Value</th>
                                                     </tr> --}}
                                                 </thead>
                                                 <tbody>
                                                     <tr class="width-100 border_">
                                                        <td class="text-left w-50">{{ strtolower($value__->stocks) }}</td> {{--Category grouped--}}
                                                        <td class="text-left w-50">
                                                            <span style="color: darkgrey;">Open:</span> 
                                                            {{$value__->opening_stock}}
                                                            <span style="color: darkgrey;">Close:</span> 
                                                            {{$value__->closing_stock}} ({{ strtolower($value__->unit) }})
                                                        </td>
                                                     </tr>
                                                 </tbody>
                                            </table>
                                         @endforeach
                                        </td>
                                    </tr>
                                </tbody>
                           </table>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="text-xs .capitalize ">
                    <td colspan="5">Date: {{ \Carbon\Carbon::parse($Date)->format('l/F/Y') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>