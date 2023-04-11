<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>stockreport</title>
</head>
<body>
    <div style="margin-bottom: 20px;">
        <h1 style="text-align: center; margin: 0px; padding: 0px;">Stock Report</h1>
        <h1 style="text-align: center;  text-transform: capitalize;  font-weight: 700;  font-size: 1.25rem; line-height: 1.75rem;">
            macsedo Resort hotel
        </h1>
        <div style="text-align: center; width: 100%;">
            <span style=" font-size: 0.75rem; line-height: 1rem;">Kabuusu,Masaka Road</span>
            <span style=" font-size: 0.75rem; line-height: 1rem;">P.O. Box 73369, Kampala, +256 Uganda</span>
            <span style=" font-size: 0.75rem; line-height: 1rem;">Phone/Fax:  +256-414272675 / +256-776650268 </span>
            <span style=" font-size: 0.75rem; line-height: 1rem;">Email:  info@macsedoresorthotel.com</span>
        </div>
    </div>

    <div style="width: 100%;">
        <div style=" width: 100%; color:rgb(242, 4, 4); background-color: rgba(107, 114, 128, 0.5); text-align: left; padding-top: 5px; padding-bottom:5px; padding-left: 2px">
            {{ $stock[0]->section }}
        </div>
        <div style=" width: 100%; background-color: rgba(107, 114, 128, 0.5); text-align: left; padding-left: 2px">
            Report Date:  {{ \Carbon\Carbon::now()->setTimezone('Africa/Kampala')->format('M, d Y H:i:s A') }}
        </div>
        <div style=" width: 100%; background-color: rgba(107, 114, 128, 0.5); text-align: left; padding-bottom: 5px; padding-left: 2px">
            Stock Taking Date:  {{ $Date }}
        </div>
        <div style=" width: 100%; margin-top: 5px">
            <table style=" width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style=" width: 100%; background-color: rgba(107, 114, 128, 0.5); text-transform: uppercase; padding-top: 5px; padding-bottom: 5px; padding-left: 2px;">
                        <th style="text-align: left; width: 25%;">category</th>
                        <th style="text-align: left; width: auto;">stocks</th>
                        <th style="text-align: left; width: auto;">opening</th>
                        <th style="text-align: left; width: auto;">closing</th>
                    </tr>
                </thead>
                <tbody>
                    @php ($i = null)
                    @foreach ($stock ?? '' as $key=>$value )
                        @if($i === $value->category) 
                            <tr style=" width: 100%;">
                                <td style="text-align: left; width: 25%; background-color: rgba(107, 114, 128, 0.5); padding-left: 2px; text-transform: uppercase;"></td>         
                                <td style="text-align: left; width: auto; white-space: nowrap; padding-left: 2px;">{{ strtolower($value->stocks) }}</td>
                                <td style="text-align: left; width: auto; padding-left: 2px;">{{ strtolower($value->opening_stock) }} {{ strtolower($value->unit) }}</td>
                                <td style="text-align: left; width: auto; padding-left: 2px;">{{ strtolower($value->closing_stock) }} {{ strtolower($value->unit) }}</td>
                            </tr>
                        @else
                            @php ($i = $value->category)
                            <tr style=" width: 100%; border-top:: 1px solid rgba(107, 114, 128, 0.5);">
                                <td style="text-align: left; width: 25%; background-color: rgba(107, 114, 128, 0.5); padding-left: 2px; text-transform: uppercase;">{{ strtolower($value->category) }}</td>       
                                <td style="text-align: left; width: auto; white-space: nowrap; padding-left: 2px;">{{ strtolower($value->stocks) }}</td>
                                <td style="text-align: left; width: auto; padding-left: 2px;">{{ strtolower($value->opening_stock) }} {{ strtolower($value->unit) }}</td>
                                <td style="text-align: left; width: auto; padding-left: 2px;">{{ strtolower($value->closing_stock) }} {{ strtolower($value->unit) }}</td>
                            </tr>       
                        @endif

                    @endforeach 
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>