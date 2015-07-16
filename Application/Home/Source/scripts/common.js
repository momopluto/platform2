
$(function() {

    // ----------------------------以下代码作用：展示顾客订单记录
    
    $('.clientHisOrder').on('click', function() {

        event.preventDefault();
        /* Act on the event */
        $("#clientHisOrder-modal").modal("toggle");

        $('.orders_data tr').remove();
         $.ajax({
            url: HisOrder_URL,
            type: 'post',
            data:{
              phone:$(this).attr("phone"),
              rid:$(this).attr("rid"),
              id:$(this).attr("id")
            },
            dataType: 'json',
            success: function(data) {

              // console.log(data);
              // return;

              var tr, td_pasttime, td_total, td_info, td_address, td_phone;
               $.each(data, function(i, item){
                    $tr=$('<tr></tr>');
                    $td_pasttime=$("<td></td>").append(item['pasttime']);
                    $td_total=$("<td></td>").append(item['total']);
                    $td_info=$("<td></td>").append(item['info']);
                    $td_address=$("<td></td>").append(item['address']);
                    $td_phone=$("<td></td>").append(item['phone']);
                    $tr.append($td_pasttime).append($td_total).append($td_info).append($td_address).append($td_phone);
                    $('.orders_data').append($tr);
              });
            }
        })
    })

    // ----------------------------以下代码作用：将订单设为无效

    var guid_value="";
    var rid_value="";

    $(".get-guid").on('click', function() {
        event.preventDefault();
        /* Act on the event */
        guid_value=$(this).attr("guid");
        rid_value=$(this).attr("rid");

        // alert(guid_value);
    });

    $(".btn-3").on('click', function(){

        $("#setInvalid-modal").modal("toggle");
    });

    $(".setInvalid").on('click', function(){ 

        $.ajax({
                url: Invalid_URL,
                type: 'post',
                data:{
                      reason:$(".Invalid_reason option:selected").val(),
                      guid:guid_value,
                      rid:rid_value
                    },
                //dataType: 'json',
                success: function(data) {

                   // console.log(data);

                   alert(data['info']);
                   window.location.reload();
                }
        });
    });

});