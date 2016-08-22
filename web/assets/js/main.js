$(document).ready(function(){
	var newTableColumnCount = 0;
	$('#new-table-column').click(function(){
		newTableColumnCount += 1;
		var newColumn = 	'<tr>'+ 
                                '<td> <input type="text" name="field_name['+newTableColumnCount+']"> </td>'+ 
                                '<td>'+
                                    '<select  name="field_type['+newTableColumnCount+']">'+
                                        '<option elected="selected" value="varchar">varchar</option>'+
                                        '<option value="int">integer</option>'+
                                        '<option value="text">text</option>'+
                                        '<option value="timestamp">datetime</option>'+
                                      '</select>'+
                                '</td>'+ 
                                '<td><input type="text"  name="field_length['+newTableColumnCount+']"></td>'+ 
                                '<td><input type="checkbox"  name="field_null['+newTableColumnCount+']" ></td>'+ 
                              '</tr>'; 
		$('#newtable-create tbody').append(newColumn);
	});
});