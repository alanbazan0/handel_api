/*alanbazan
version 2.0
17/06/2021
*/
class ArrayUtils
{
	static copy(array)
	{
		var newArray = [];
		for(var i=0; i < array.length; i++)
		{
			var item = array[i];
			var clone = Object.assign( Object.create( Object.getPrototypeOf(item)), item)
			newArray.push(clone);
		}
		return newArray;
	}
	
	static shuffle(array) 
	{
		  var currentIndex = array.length, temporaryValue, randomIndex;

		  // While there remain elements to shuffle...
		  while (0 !== currentIndex) {

		    // Pick a remaining element...
		    randomIndex = Math.floor(Math.random() * currentIndex);
		    currentIndex -= 1;

		    // And swap it with the current element.
		    temporaryValue = array[currentIndex];
		    array[currentIndex] = array[randomIndex];
		    array[randomIndex] = temporaryValue;
		  }

		  return array;
	}
	
	static groupBy(fields, array)
	{
		var groups= [];
		if(array!=undefined)
		{
			var fieldsArray = fields.split(",");
			
			for(var i = 0; i < array.length; i++)
			{
				var item = array[i];
				var group = ArrayUtils.searchGroup(item,fieldsArray,groups);
				if(group==null)
				{
					group = new Object;
					for(var j=0; j<fieldsArray.length;j++)
					{
						group[fieldsArray[j]] = item[fieldsArray[j]];
					}	
					group.data = [];
					group.data.push(item);
					groups.push(group);
				}	 
				else
				{		
					group.data.push(item);
				}
			}		
			//orderBy(fields,groups);		
		}
		return groups;
	}
	
	static searchGroup(item,arrayFields,groups)
	{
		var val1 = ArrayUtils.getValues(item,arrayFields);			
		for(var i= 0;i < groups.length;i++)
		{
			var val2 = ArrayUtils.getValues(groups[i],arrayFields);
			if(ArrayUtils.equalValues(val1,val2))
				return groups[i];
		}		
		return null;
	}
	

	static equalValues(values1,values2)
	{
		for(var i=0; i< values1.length;i++)
		{
			if(values1[i]!=values2[i])
				return false;
		}
		return true;	
	}
	
//	static getValues(item,fieldsArray)
//	{
//		var values = [];
//		for(var i=0; i< fieldsArray.length;i++)
//		{
//			var value = item[fieldsArray[i]];
//			values.push(value);
//		}
//		return values;
//	}
	static getValues(item,fieldsArray)
	{
		var values = [];
		for(var i=0; i< fieldsArray.length;i++)
		{
			var field = fieldsArray[i];
			var value = ArrayUtils.getValue(item,field);
			values.push(value);
		}
		return values;
	}
	
	static getValue(item, field)
	{
		var fieldsArray = field.split(".");
		if(fieldsArray.length==1)
			return item[field];
		else
		{
			if(fieldsArray.length>0)
			{
				var obj = item[fieldsArray[0]];
				for (var i = 1; i < fieldsArray.length; i++) 
				{
					var f = fieldsArray[i];
					obj = obj[f]; 
				}
			}
			return obj;
		}
	}
	
	static searchWithValues(fields,values,array)
	{
		if(array!=null)
		{
			var fieldsArray = fields.split(",");						
			for(var i=0;i < array.length;i++)
			{
				var val2 = ArrayUtils.getValues(array[i],fieldsArray);
				if(ArrayUtils.equalValues(values,val2))
					return array[i];
			}		
		}
		return null;
	}
	
	static existsWithValues(fields,values,array)
	{
		if(array!=null)
		{
			var fieldsArray = fields.split(",");						
			for(var i=0;i < array.length;i++)
			{
				var val2 = ArrayUtils.getValues(array[i],fieldsArray);
				if(ArrayUtils.equalValues(values,val2))
					return true
			}		
		}
		return false;
	}
	
	static removeWithValues(fields,values,array)
	{
		if(array!=null)
		{
			var fieldsArray = fields.split(",");						
			for(var i=0;i < array.length;i++)
			{
				var val2 = ArrayUtils.getValues(array[i],fieldsArray);
				if(ArrayUtils.equalValues(values,val2))
				{
					array	.splice(i, 1);
				}
			}		
		}
		return null;
	}
	
	static indexWithValues(fields,values,array)
	{
		if(array!=null)
		{
			var fieldsArray = fields.split(",");						
			for(var i=0;i < array.length;i++)
			{
				var val2 = ArrayUtils.getValues(array[i],fieldsArray);
				if(ArrayUtils.equalValues(values,val2))
					return i;
			}		
		}
		return -1;
	}
	
	
	static getIndexWithValues(fields,values,array)
	{
		if(array!=null)
		{
			var fieldsArray = fields.split(",");						
			for(var i=0;i < array.length;i++)
			{
				var val2 = ArrayUtils.getValues(array[i],fieldsArray);
				if(ArrayUtils.equalValues(values,val2))
					return i;
			}		
		}
		return -1;
	}
	
	static filterWithValues(fields,values,array)
	{
		var newArray = [];
		if(array!=null)
		{
			var fieldsArray = fields.split(",");						
			for(var i=0;i < array.length;i++)
			{
				var val2 = ArrayUtils.getValues(array[i],fieldsArray);
				if(ArrayUtils.equalValues(values,val2))
					newArray.push(array[i]);
			}		
		}
		return newArray;
	}
	
	static getIndexWithMaxValue(arreglo,propiedad)
	{
		var max = 0;
		var index = -1;
		if(arreglo!=null)
			if(arreglo.length>0)
			{
				var elemento = arreglo[0];
				max = elemento[propiedad];
				index = 0;
				for(var i =1;i<arreglo.length;i++)
				{
					elemento = arreglo[i];
					if(elemento[propiedad]>max)
					{
						max = elemento[propiedad];
						index= i;
					}
				} 	
			}				
		return index;
	}
	
	static getIndexWithMinValue(arreglo,propiedad)
	{
		var max = 0;
		var index = -1;
		if(arreglo!=null)
			if(arreglo.length>0)
			{
				var elemento = arreglo[0];
				max = elemento[propiedad];
				index = 0;
				for(var i =1;i<arreglo.length;i++)
				{
					elemento = arreglo[i];
					if(elemento[propiedad]<max)
					{
						max = elemento[propiedad];
						index= i;
					}
				} 	
			}				
		return index;
	}
	
	static getMax(arreglo,propiedad)
	{
		var max = 0;
		if(arreglo!=null)
			if(arreglo.length>0)
			{
				var elemento = arreglo[0];
				max = elemento[propiedad];
				for(var i =1;i<arreglo.length;i++)
				{
					elemento = arreglo[i];
					if(elemento[propiedad]>max)
						max = elemento[propiedad];
				} 	
			}				
		return max;
	}
	
	static join(array, field)
	{
		var values = [];
		for(var i =0;i<array.length;i++)
		{
			var row = array[i];
			var value = row[field];
			values.push(value);
		}
		return values.join();
	}
	
	static sortBy(property, array)
	{
		return array.sort(ArrayUtils.dynamicSort(property))
	}
	
	static dynamicSort(property) {
	    var sortOrder = 1;
	    if(property[0] === "-") {
	        sortOrder = -1;
	        property = property.substr(1);
	    }
	    return function (a,b) {
	        /* next line works with strings and numbers, 
	         * and you may want to customize it to your needs
	         */
	        var result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
	        return result * sortOrder;
	    }
	}

}