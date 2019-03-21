	/**
	*
	* @param {String} HttpUrl - The Url to post to
	* @param {Object} CallBackContext - The object which contains the CallBackFunction
	* @param {Method} CallBackFunction - The call back function
	* @param {String} PostMethod - The get/post method
	* @param {String} Parameters - Parameters to pass in the get/post string
	* @param {Object} ContextHandler - This is an instance of our  global context handler. That allows concurrent
	* ajax calls correctly.
	*/
	function Ajaxv2(HttpUrl,CallBackContext,CallBackFunction,PostMethod,Parameters,ContextHandler){   
		this.HttpUrl        = HttpUrl;
		this.PostMethod  = PostMethod;
		this.Parameters  = Parameters;
		this.ContextHandler = ContextHandler;
		this.Handled        = false;   
		this.Version        = 2.0;
		this.Request        = null;
		this.EventData    = null;
		GlobalAjaxv2Context = this; //really only for single fired events, 2 concurrent wont' work without a  context handler.
		if(typeof(ContextHandler) == "undefined"){
			this.ContextHandler=null
		}
        //this checks our state and performs the callback function on success
        this.GlobalProcessRequestAjaxv2 = function()
        {      
            //when this even fires, our context is WITHIN the XMLHttpRequest object itself
            if(typeof(ContextHandler) != "undefined"){
                /*
                 * DO NOT fire our event off our globallly stored context. we are using a Context Handler meaning we are firing 2+ events at once. We need
                 * to let the context handler do its thing.
                */
                  ContextHandler.CheckEvents();
              }         
              else{
                  GlobalAjaxv2Context.ProcessRequestAjaxv2();
				}
          }
          this.ProcessRequestAjaxv2 = function()
           { 
              try
              {
                  if(this.Request.readyState == 4)
                  {
                      //According to the HTTP 1.1 specification, any response that has a code between 200 and 299 inclusive is a successful response.
                      if(this.Request.status >= 200 && this.Request.status <= 299)
                      {
                          //CallBackFunction.call(CallBackContext,true,this.Request,this.EventData);                   
                          CallBackFunction.call(CallBackContext,this.Request.responseText);
                      }
                      else
                      {
                          //error out with the server error code and message.
                          //CallBackFunction.call(CallBackContext,false,this.Request,this.EventData);
                          CallBackFunction.call(CallBackContext,false,this.Request,this.EventData);
                      }
                  }
              }
              catch(exception)
              {
                  //alert("An exception was caught while trying to process an AJAX request. ("+exception+")");
              }                 
           } 

		   //this creates our object
           this.InitAjaxv2 = function()
           {      
               try
               {
                  HttpRequest = new XMLHttpRequest();
               }
               catch(e)
               {
                  try
                  {
                   HttpRequest = new ActiveXObject("Msxml2.XMLHTTP");
                  }
                  catch (e)
                  {
                     try{
                         HttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
                      }catch (E){
                        HttpRequest = null;
                      }
                  }
               }      
               return HttpRequest;
           }
          //this actually sends the get or post request
          this.GetPost = function(Async){
              this.Request = this.InitAjaxv2();
              if(this.Request == null)
                  return false;
              this.Request.onreadystatechange=this.GlobalProcessRequestAjaxv2;
              //setup our context handler if we need to
              if(this.ContextHandler != null)
                  this.ContextHandler.AddAjaxv2Object(this);
              this.Request.open(this.PostMethod, this.HttpUrl, Async);
              this.Request.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");
              this.Request.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT");
              this.Request.send(this.Parameters);
              /*
                MOZ browser will never fire onreadystatechange when in synchronous mode,
                but they will wait for req.send to complete. So we can fire the function here instead.
                http://www.quirksmode.org/blog/archives/2005/09/xmlhttp_notes_r_2.html
              */
              if (Async == false)
              {
                  this.GlobalProcessRequestAjaxv2();      
              }  
          }
         
		/*
		*   @name getFormValues()
		*   @descr This function creates a string of the name-value pairs for the specified form object
		*   @param (object) fobj - the form object that the values are wanted for
		*   @return str - the string containing the name-value pairs for the form object
		*/
		this.GetFormValues = function(fobj)
		{
			var str = "";
			var valueArr = null;
			var val = "";
			var cmd = "";
			var obj;

			//get all the inputs
			var objects = fobj.getElementsByTagName('input');
			for(object in objects){
                  obj = objects[object];
                  switch(obj.type)
                  {
                      case "text":               
                      case "hidden":
                          str += obj.id + "=" + escape(obj.value) + "&";       
                          break;
                         
                      case "radio":
                      case "checkbox":
                          if  (obj.checked)
                              str += obj.id + "=" + escape(obj.value) + "&";                  
                          break;
                  }
              }
             
              objects = fobj.getElementsByTagName('select');
              for(object in objects)
              {
                  obj = objects[object];
                  switch(obj.type)
                  {
                      case "select-one":
                          str += obj.id + "=" + obj.options[obj.selectedIndex].value + "&";
                          break;              
                      
                      case "select-multiple":
                          for(f=0;f<obj.length;f++)
                          {
                              if(obj.options[f].selected==true)
                                  str += obj.id + "=" + escape(obj.options[f].value) + "&";           
                          }
                          break;
                  }
              }
             
              objects = fobj.getElementsByTagName('textarea');
              for(object in objects)
              {
                  obj = objects[object]; 
                  str += obj.id + "=" + escape(obj.value) + "&";
              }
              str = str.replace(/\+/g,"%2b"); //+ are going to spaces grr!   
              str = str.substr(0,(str.length - 1));
              return str;
		}
	}