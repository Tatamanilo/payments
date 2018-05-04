{if !$xml}
<!--<script src="{$sMainSiteUrl}admin/js/jqGrid/js/jquery-1.4.2.min.js" type="text/javascript"></script>-->
<script src="{$sMainSiteUrl}admin/js/jqueryui/ui.datepicker.js" type="text/javascript"></script>
<!--<script src="{$sMainSiteUrl}admin/js/jqueryui/datetimepicker/ui/jquery-ui-1.7.3.custom.js" type="text/javascript"></script>   -->
<script src="{$sMainSiteUrl}admin/js/jqGrid/js/i18n/grid.locale-en.js" type="text/javascript"></script>
<script src="{$sMainSiteUrl}admin/js/jqGrid/js/jquery.jqGrid.min.js" type="text/javascript"></script>
<script src="{$sMainSiteUrl}admin/js/jqGrid/src/grid.subgrid.js" type="text/javascript"></script>

<script src="{$sMainSiteUrl}admin/js/jqueryui/ui.core.js" type="text/javascript"></script>
<script src="{$sMainSiteUrl}admin/js/jqueryui/ui.sortable.js" type="text/javascript"></script>

<script type="text/javascript">
    jQuery.jgrid.no_legacy_api = true;
</script>
<link href="{$sMainSiteUrl}admin/js/jqueryui/jquery-ui-1.7.3.custom.css" rel="stylesheet" type="text/css" />
<link href="{$sMainSiteUrl}admin/js/jqGrid/css/ui.jqgrid.css" rel="stylesheet" type="text/css" />


<div id="mysearch"></div>  
<br />
<table id="products_list"></table> 
<div id="pager"></div> 




      
{literal}
<script>
    jQuery(document).ready(function(){   
    
    jQuery("#products_list").jqGrid({ 
        url: "http://"+url+"/admin/index.php", 
        postData: {
            s:      "ajax", 
            ajax:   "login", 
            ev:     "viewIncomingMoney",
            xml: 1
        }, 
        mtype: "POST", 
        datatype: "xml", 
        colNames:[
            '{/literal}{$translates.date|capitalize:true|default:"Date"}', 
            '{$translates.product|capitalize:true|default:"Product"}', 
            '{$translates.type|capitalize:true|default:"Type"}', 
            '{$translates.is_commission|capitalize:true|default:"Direct/Aff."}', 
            '{$translates.commission1|capitalize:true|default:"Comission"}', 
            '{$translates.commission2|capitalize:true|default:"Pool"}', 
            '{$translates.amount|capitalize:true|default:"Amount"}', 
            '{$translates.fees|capitalize:true|default:"Fees"}', 
            '{$translates.net|capitalize:true|default:"Net"}', 
            '{$translates.status|capitalize:true|default:"Status"}{literal}'
        ], 
        colModel:[ 
            {name:'t_date', index:'t_date', width:80},                       
            {name:'product', index:'product', align:"left", width:200, sortable:false},
            {name:'type', index:'type', align:"left", width:60, sortable:false},
            {name:'isAff', index:'isAff', align:"left", width:100},
            {name:'commission1', index:'commission1', align:"right", width:100, sortable:false},
            {name:'commission2', index:'commission2', align:"right", width:60, sortable:false},
            {name:'amount', index:'amount', align:"right", width:60, sortable:false},
            {name:'fees', index:'fees', align:"right", width:60, sortable:false},
            {name:'t_amount', index:'t_amount', align:"right", width:60},
            {name:'c_subaccount', index:'c_subaccount', align:"left"}
        ], 
        rowNum:10, 
        rowList:[10,20,30], 
        pager: jQuery('#pager'),     
        forceFit: true,
        sortname: 't_date', 
        viewrecords: true, 
        sortorder: "desc", 
        height: "100%", 
        width: 970, 
        //caption:"{/literal}{$translates.my_purchases|capitalize:true}{literal}",
        subGrid : true,   
        subGridRowExpanded: function(subgridid,id)
        {
            $("#"+jQuery.jgrid.jqID(subgridid)).html($("#div_subinfo_" + id).html());
        }
    });
    jQuery("#products_list").jqGrid('sortableRows'); 

    /*
    jQuery("#mysearch").jqGrid('filterGrid', "products_list",{
        gridModel:false,
        filterModel: [
            {label:'{/literal}{$translates.date_start|capitalize:true|default:"Start Date"}{literal}', name: 'date_start', stype: 'text'},
            {label:'{/literal}{$translates.date_end|capitalize:true|default:"End Date"}{literal}', name: 'date_end', stype: 'text'},
            {label:'{/literal}{$translates.type|capitalize:true|default:"Type"}{literal}', name: 'type', stype: 'select', defval: '', sopt:{value:':;Tips:Tips;Sales:Sales'}},
            {label:'{/literal}{$translates.is_commission|capitalize:true|default:"Is Comission"}{literal}', name: 'is_commission', stype: 'select', defval: '', sopt:{value:':;Yes:Yes;No:No'}},
            {label:'{/literal}{$translates.net_min|capitalize:true|default:"Net From"}{literal}', name: 'net_min', stype: 'text'},
            {label:'{/literal}{$translates.net_max|capitalize:true|default:"Net To"}{literal}', name: 'net_max', stype: 'text'}
        ],
        formtype:"vertical",
        autosearch: true,
        enableSearch: true,
        enableClear: true,
        buttonclass: "buttonOrange buttonCorners5"
    });
    
    var dates = $( "#sg_date_start, #sg_date_end" ).datepicker({
        dateFormat: "yy-mm-dd",
        onSelect: function( selectedDate ) {
            var option = this.id == "sg_date_start" ? "minDate" : "maxDate",
                instance = $( this ).data( "datepicker" );
            date = $.datepicker.parseDate(
                instance.settings.dateFormat ||
                $.datepicker._defaults.dateFormat,
                selectedDate, instance.settings );
            dates.not( this ).datepicker( "option", option, date );
        }
    });
    */ 
    });   
</script>
{/literal}

{else}

<?xml version ="1.0" encoding="utf-8"?>
<rows>
    <page>{$page}</page>
    <total>{$pages_count}</total>
    <records>{$records_count}</records>
    {foreach from=$items item=item key=k}
        <row id='{$k}'>
            <cell><![CDATA[<div class="">{$item.date|date_format:"%m-%d-%y"}</div>]]></cell>
            <cell><![CDATA[<div class="">{$item.product.name|truncate:"30"|default:""}</div>]]></cell>
            <cell><![CDATA[<div class="">{$item.type}</div>]]></cell>
            <cell><![CDATA[<div class="">{if $item.is_comission}Affiliate{else}Direct{/if}</div>]]></cell>
            {*<cell><![CDATA[<div class="">{$item.buyer_id}</div>]]></cell>
            <cell><![CDATA[<div class="">{$item.author_id}</div>]]></cell>
            <cell><![CDATA[<div class="">{$item.aff1.id}</div>]]></cell>
            <cell><![CDATA[<div class="">{$item.aff2.id|default:""}</div>]]></cell>*}
            <cell><![CDATA[<div class="">{if $item.commission1}{$item.commission1|string_format:"%.2f"}{else}-{/if}</div>]]></cell>
            <cell><![CDATA[<div class="">{if $item.commission2}{$item.commission2|string_format:"%.2f"}{else}-{/if}</div>]]></cell>
            <cell><![CDATA[<div class="">{if $item.total_amount}{$item.total_amount|string_format:"%.2f"}{else}-{/if}</div>]]></cell>
            <cell><![CDATA[<div class="">{if $item.fee}{$item.fee|string_format:"%.2f"}{else}-{/if}</div>]]></cell>
            <cell><![CDATA[<div class="">{$item.net|string_format:"%.2f"}</div>]]></cell>
            <cell><![CDATA[<div class="">{$item.status}
                <div id="div_subinfo_{$k}" style="display:none;">
                    {$translates.entry_id|capitalize:true|default:"Entry Id"}: {$item.entry_id}<br />
                    {$translates.trans_id|capitalize:true|default:"Transaction Id"}: {$item.tranz_id}<br />
                    {$translates.buyer|capitalize:true|default:"Buyer"}: {$item.buyer.info.first} {$item.buyer.info.last}<br />
                    {$translates.author|capitalize:true|default:"Author"}: {$item.author.info.first} {$item.author.info.last}<br />
                    {$translates.affiliate1|capitalize:true|default:"Affiliate 1"}: {$item.aff1.info.first|default:""} {$item.aff1.info.last|default:""}<br />
                    {$translates.affiliate2|capitalize:true|default:"Affiliate 2"}: {$item.aff2.info.first|default:""} {$item.aff2.info.last|default:""}<br />
                </div>
            </div>]]></cell>
        </row>
    {/foreach}
</rows>

{/if}
