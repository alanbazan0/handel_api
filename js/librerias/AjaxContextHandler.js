/**
* Global Ajax Context Handler. This is simply a global class that keeps track of all ajax requests we've made and when told to, will loop through them to see if there are
* any that need to be taken care of. By housing all instances in a single spot, we  can simply tell this  class to CheckEvents and it will loop through its internal
* array of ajax objects to see which objects have have completed, yet haven't been handled. This way it doesn't care who actually STARTED this request, it will always
* fire the right handler for the right ajax object.
*/
function AjaxContextHandler()
{
	this.AjaxObjects = Array();
 
	this.AddAjaxv2Object = function(object){
	  this.AjaxObjects.push(object);   
	}
	
	this.CheckEvents = function(){         
		for(var i=0;i<this.AjaxObjects.length;i++){
			var obj=this.AjaxObjects[i];
			if(obj != null && !obj.Handled){
				if(obj.Request != null && obj.Request.readyState == 4){
					obj.Handled = true;
					obj.Fired   = true;
					obj.ProcessRequestAjaxv2(); //get back into context ;)
					this.AjaxObjects[i] = null; //free up our array;
				}            
			}
		}
	}   
}