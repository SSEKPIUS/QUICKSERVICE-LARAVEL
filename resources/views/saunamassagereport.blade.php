<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SaunaMassage</title>
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
    .w-50{
        width:50%;   
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
    .font-bold {
        font-weight: 700;
    }
    .text-sm {
        font-size: 0.875rem;
        line-height: 1.25rem;
    }

    .text-base {
        font-size: 1.5rem;
        line-height: 1.25rem;
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

</style>
<body>
    <div class="head-title">
        <h1 class="text-center m-0 p-0 text-base">Cash Flow Statement</h1>
        <h1 class="text-center m-0 p-0 text-sm">(STEAM SAUNA & MASSAGE)</h1>
        <h1 class=" text-center capitalize font-bold text-xl">
            macsedo Resort hotel
        </h1>
        <div class="text-center  w-full flex flex-col text-sm">
            <span class=" text-xs">Kabuusu,Masaka Road</span>
            <span class=" text-xs">P.O. Box 73369, Kampala, +256 Uganda</span>
            <span class=" text-xs">Phone/Fax:  +256-414272675 / +256-776650268 </span>
            <span class=" text-xs">Email:  info@macsedoresorthotel.com</span>
        </div>
    </div>
    <div class="width-100 mt-10 text-xs">
        <table class="tableCustom w-50 mt-10">
            <thead>
                <tr>
                    <th class="w-50 text-left ">Section</th>
                    <th class="w-50 text-left">Evaluation</th>
                </tr>
            </thead>
            <tr>
                <td class="box-text">
                    <div>
                        <p>Cash:</p>
                        <p>Cash Equivalent:</p>
                        <p>Cash Projection:</p>
                    </div>
                </td>
                <td>
                    <div class="p-x">
                        <p>{{$sumPaid ?? ''}}</p>
                        <p>{{$sumHeldPaid ?? '' }}</p>
                        <p>{{$totalArrears ?? ''}}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="width-100">
        <table class="width-100">
            <thead>
                @if(!empty($Dfrom) && !empty($Dto))         
                    <tr class="text-sm .capitalize ">
                        <td colspan="7">Cash Position Between {{$Dfrom}} and {{$Dto}}</td>
                    </tr>       
                @elseif(!empty($Date_))
                    <tr class="text-sm .capitalize ">
                        <td colspan="7">Cash Position on {{$Date_}}</td>
                    </tr>     
                @endif
                <tr class="width-100 table-header">
                    <th class="text-left">Name</th>
                    <th class="text-left">Service</th>
                    <th class="text-left">Fee</th>
                    <th class="text-left">Status</th>
                    <th class="text-left">Checked In</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($guests ?? '' as $data )
                <tr class="width-100">
                    <td class="text-left">{{$data->fullname}}</td>
                    <td class="text-left">{{$data->service}}</td>
                    <td class="text-left">{{$data->fee}}</td>
                    <td class="text-left">@switch($data->paid)
                        @case(0)
                            <span class="red">UNPAID</span>
                            @break
                        @case(1)
                            <span class="green">PAID</span>
                            @break
                        @default
                            <span class="brown">UNKNOWN</span>
                    @endswitch</td>
                    <td class="text-left">{{ \Carbon\Carbon::parse($data->created_at)->format('l/F/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="text-xs .capitalize ">
                    <td colspan="5">C/0 Macsedo Resort Hotel</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>