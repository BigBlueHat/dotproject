<script type="text/javascript">
<!--
        function hideAll()
        {       
                var selobj = document.getElementById('htmltype');
                for (i = 0; i < selobj.options.length; i++)
                {       
                        var atbl = document.getElementById('atbl_'+selobj.options[i].value);
                        var adiv = document.getElementById('div_'+selobj.options[i].value);
                        
                        atbl.style.visibility = 'hidden';
                        adiv.style.display = 'none';
                } 
        }
        
        function showAttribs()
        {       
                hideAll();
                
                var selobj = document.getElementById('htmltype');
                var seltype = selobj.options[selobj.selectedIndex].value;
                
                var atbl = document.getElementById('atbl_'+seltype);
                var adiv = document.getElementById('div_'+seltype);
                atbl.style.visibility = 'visible';
                adiv.style.display = 'block';
        }
        
        function addSelectItem()
        {       
                frm = document.getElementById('custform');
                frm.action = '?m=system&a=custom_field_addedit';
                frm.submit();
        }

        function deleteItem( itmname )
        {
                del = document.getElementById('delete_item');
                del.value = itmname;
                addSelectItem();
        }

        function postCustomField()
        {
                frm = document.getElementById('custform');
                frm.action = '?m=system&a=custom_field_editor';
                sql = document.getElementById('dosql');
                sql.name = 'dosql';     
                frm.submit();
        }
-->
</script>
