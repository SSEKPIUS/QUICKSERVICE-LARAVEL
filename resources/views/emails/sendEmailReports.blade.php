<?php echo  $usermail; ?>
<div
   style="text-align:center;min-width:640px;width:100%;height:100%;font-family:&quot;Helvetica Neue&quot;,Helvetica,Arial,sans-serif;margin:0;padding:0"
   bgcolor="#fafafa">
   <table border="0" cellpadding="0" cellspacing="0" id="m_1946288138506856919body"
      style="text-align:center;min-width:640px;width:100%;margin:0;padding:0" bgcolor="#fafafa">
      <tbody>
         <tr>
            <td style="font-family:&quot;Helvetica Neue&quot;,Helvetica,Arial,sans-serif;height:4px;font-size:4px;line-height:4px"
               bgcolor="#6b4fbb"></td>
         </tr>
         <tr>
            <td
               style="font-family:&quot;Helvetica Neue&quot;,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.6;color:#5c5c5c;padding:25px 0">
               <img alt="mail"
                  src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSOrTZ676XrWOIucKsydBt-iTqZQAMeR0JQNA&usqp=CAU"
                  width="140" height="100" class="CToWUd">
            </td>
         </tr>
         <tr>
            <td style="font-family:&quot;Helvetica Neue&quot;,Helvetica,Arial,sans-serif">
               <table border="0" cellpadding="0" cellspacing="0" class="m_1946288138506856919wrapper"
                  style="width:640px;border-collapse:separate;border-spacing:0;margin:0 auto">
                  <tbody>
                     <tr>
                        <td class="m_1946288138506856919wrapper-cell"
                           style="font-family:&quot;Helvetica Neue&quot;,Helvetica,Arial,sans-serif;border-radius:3px;overflow:hidden;padding:18px 25px;border:1px solid #ededed"
                           align="left" bgcolor="#fff">
                           <table border="0" cellpadding="0" cellspacing="0"
                              style="width:100%;border-collapse:separate;border-spacing:0">
                              <tbody>
                                 <tr>
                                    <td style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#333333;font-size:15px;font-weight:400;line-height:1.4;padding:15px 5px"
                                       align="center">
                                       <div id="m_1946288138506856919content">
                                          <h1 style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#333333;font-size:18px;font-weight:400;line-height:1.4;margin:0;padding:0"
                                             align="center">Welcome, <?php echo  $usermail; ?>!</h1>
                                          <p>Find Attached the Daily Reports.</p>
                                          <div id="m_1946288138506856919cta">
                                             <div><?php echo $body; ?></div>
                                          </div>
                                       </div>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
            <td
               style="font-family:&quot;Helvetica Neue&quot;,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.6;color:#5c5c5c;padding:25px 0">
            </td>
         </tr>
      </tbody>
   </table>
   <div class="yj6qo"></div>
   <div class="adL">
   </div>
   <div>
      <?php
         $chart = "{
            type:'doughnut',
            data:{
               labels:['Rooms','Sauna_Masssage','Bar_Kitchen'],
               datasets:[
                  {
                     data: [" . implode(",", $doughnut) . "]
                  }
               ]
            },
            options:{
               plugins:{
                  doughnutlabel:{
                     labels:[
                        {
                           text:'" . array_sum($doughnut) . "',
                           font:{
                              size:20
                           }
                        },
                        {
                           text:'total (ugx)'
                        }
                     ]
                  }
               }
            }
         }";

         $chart2 = "{
            type: 'line',
            data: {
               labels: [" . implode(",", $line_labels) . "],
               datasets: [
                  {
                     label: 'Rooms',
                     data: [" . implode(",", $line_rooms) . "],
                     fill: false,
                     borderWidth: 1
                  },
                  {
                     label: 'Sauna_Masssage',
                     data: [" . implode(",", $line_sauna_masage) . "],
                     fill: false,
                     borderWidth: 1
                  },
                  {
                     label: 'Bar_Kitchen',
                     data: [" . implode(",", $line_bar_kitchen) . "],
                     fill: false,
                     borderWidth: 1
                  }
               ],
            },
            options: {
               legend: {
                  display: true,
                  position: 'top',
                  align: 'start'
               }
            }
         }";
         $encoded = urlencode($chart);
         $imageUrl = "https://quickchart.io/chart?c=" . $encoded;
         echo "<img src=$imageUrl style='height: 350px; width: auto;' />";

         $encoded = urlencode($chart2);

         //dd($encoded);

         $imageUrl = "https://quickchart.io/chart?c=" . $encoded;
         echo "<img src=$imageUrl style='height: 350px; width: auto;' />";
      ?>
   </div>
</div>