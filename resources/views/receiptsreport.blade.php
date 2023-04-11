<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bar / Kitchen</title>
</head>
<style>
    .m-0{
        margin: 0px;
    }
    .p-0{
        padding: 0px;
    }
    .yellow{
        color: yellow;
    }
    .blue{
        color: blue;
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

    /*Tail Wind*/
    .bg-gray-100:nth-child(even) {
        --tw-bg-opacity: 1;
        background-color: rgba(243, 244, 246, var(--tw-bg-opacity));
    }

    .mx-auto {
        margin-left: auto;
        margin-right: auto;
    }

    .rounded-sm {
        border-radius: 0.125rem;
    }

    .text-gray-700 {
        --tw-text-opacity: 1;
        color: rgba(55, 65, 81, var(--tw-text-opacity));
    }

    .h-auto {
        height: auto;
    }

    .flex {
        display: flex;
    }

    .flex-1 {
        flex: 1 1 0%;
    }

    .p-3 {
        padding: 0.75rem;
    }

    .border-r-2 {
        border-right-width: 2px;
    }

    .text-sm {
        font-size: 0.875rem;
        line-height: 1.25rem;
    }

    .text-base {
        font-size: 1.5rem;
        line-height: 1.25rem;
    }

    .leading-5 {
        line-height: 1.25rem;
    }

    .font-semibold {
        font-weight: 600;
    }

    .font-normal {
        font-weight: 400;
    }

    .text-xs {
        font-size: 0.75rem;
        line-height: 1rem;
    }

    .leading-4 {
        line-height: 1rem;
    }

    .font-normal {
        font-weight: 400;
    }

    .text-gray-500 {
        --tw-text-opacity: 1;
        color: rgba(107, 114, 128, var(--tw-text-opacity));
    }

    .font-extrabold {
        font-weight: 800;
    }

    .ml-3 {
        margin-left: 0.75rem;
    }

    .py-1 {
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
    }

    .capitalize {
        text-transform: capitalize;
    }

    .px-6 {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    .py-2 {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }
    
    .whitespace-nowrap {
        white-space: nowrap;
    }

    .border-b {
        border-bottom: 1px solid gray;
    }
</style>
<body>
    <div class="head-title">
        <h1 class="text-center m-0 p-0 text-base">Cash Flow Statement</h1>
        <h1 class="text-center m-0 p-0 text-sm">(BAR / KITCHEN)</h1>
        <h1 class=" text-center capitalize font-bold text-xl text-xs">
            macsedo Resort hotel
        </h1>
        <div class="text-center  w-full flex flex-col text-xs">
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
                        <td colspan="7">Cash Position Between {{$Dfrom ?? 'No-Date'}} and {{$Dto ?? 'No-Date'}}</td>
                    </tr>       
                @elseif(!empty($Date_))
                    <tr class="text-sm .capitalize ">
                        <td colspan="7">Cash Position on {{$Date_ ?? 'No-Date'}}</td>
                    </tr>     
                @endif
                <tr class="width-100 table-header">
                    <th class="text-left"></th>
                    <th class="text-left"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($receipts ?? '' as $invoice )
                <tr class="bg-gray-100">
                    <td class="mx-auto rounded-sm text-gray-700 h-auto whitespace-nowrap">
                      <div class="flex p-3">
                        <div class="space-y-1 border-r-2 pr-3">
                          <div class="text-sm leading-5 font-semibold">
                            <span class="text-xs leading-4 font-normal text-gray-500"> Invoice #</span> <span class=" font-extrabold text-xs"> {{ $invoice->receipts_id }} </span>
                          </div>
                          <div class="text-sm leading-5 font-semibold">
                            <span class="text-xs leading-4 font-normal text-gray-500 pr"> Total:</span> <span class=" font-extrabold text-xs"> UGX: {{ $invoice->TTotal }} </span>
                          </div>
                          <div class="text-sm leading-5 font-semibold text-green-500 text-xs">
                            {{ $invoice->section }}
                          </div>
                          <div class="text-xm leading-5 font-semiboldtext-red-700 text-xs">
                            {{ $invoice->name }}
                          </div>
                          <div class="text-xs leading-5 font-semibold text-gray-500">
                            {{ date("F jS, Y", strtotime((string)$invoice->created_at)) }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="text-left">
                        <div class="flex-1">
                            <div class="ml-3 space-y-1 pr-3">
                                <div class="text-sm leading-4 font-normal">
                                    <div class="mx-auto">
                                        <div class="bg-white shadow-md rounded">
                                          <table class="text-left w-full border-collapse">
                                            <!--Border collapse doesn't work on this site yet but it's available in newer tailwind versions -->
                                            <thead>
                                              <tr class="border-b-2">
                                                <th class="py-1  px-1 bg-grey-lightest font-medium  capitalize text-xs  border-b border-grey-light text-center text-black">
                                                  Item
                                                </th>
                                                <th class="py-1  px-1 bg-grey-lightest font-medium  capitalize text-xs  border-b border-grey-light text-center text-black">
                                                  Description
                                                </th>
                                                <th class="py-1  px-1 bg-grey-lightest font-medium  capitalize text-xs  border-b border-grey-light text-center text-black">
                                                  qty
                                                </th>
                                                <th class="py-1  px-1 bg-grey-lightest font-medium  capitalize text-xs  border-b border-grey-light text-center text-black">
                                                  cost
                                                </th>
                                                <th class="py-1  px-1 bg-grey-lightest font-medium  capitalize text-xs  border-b border-grey-light text-center text-black">
                                                </th>
                                              </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($invoice->orders ?? '' as $orderTR )
                                                <tr>
                                                    <td class="py-2  px-6  text-center text-xs">
                                                      {{ $orderTR->dish }}
                                                    </td>
                                                    <td class="py-2  px-6  text-center text-xs">
                                                      {{ $orderTR->Description }}
                                                    </td>
                                                    <td class="py-2  px-6  text-center text-xs">
                                                      {{ $orderTR->qty }}
                                                    </td>
                                                    <td class="py-2  px-6  text-center text-xs">
                                                      {{ $orderTR->cost }}
                                                    </td>
                                                    <td class="py-2  px-6 text-right uppercase font-extrabold text-white text-sm">
                                                        <span>
                                                        @switch($orderTR->status)
                                                            @case(5)
                                                                <span class="yellow">pending</span>
                                                                @break
                                                            @case(10)
                                                                <span class="blue">new</span>
                                                                @break
                                                            @case(15)
                                                                 <span class="green">served</span>
                                                                @break
                                                            @case(20)
                                                                 <span class="red">cancelling</span>
                                                                @break
                                                            @case(25)
                                                                <span class="red">cancelled</span>
                                                                @break
                                                            @default
                                                                 <span class="red">cancelled</span>
                                                        @endswitch
                                                        </span>
                                                    </td>
                                                  </tr>
                                                @endforeach
                                            </tbody>
                                          </table>
                                        </div>
                                      </div>
                                </div>
                            </div>
                        </div>
                    </td>
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