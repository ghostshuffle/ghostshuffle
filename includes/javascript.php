    <script type="text/javascript">
        const api = {
          set_parameters: function(parameters) {
            if (typeof parameters == "object") {
              for (const parameter_key in parameters) {
                Object.defineProperty(api_parameters, parameter_key, {
                  configurable: true,
                  enumerable: true,
                  value: parameters[parameter_key],
                  writable: false
                })
              }
            }
          },
          send: function(callback) {
            const request = new XMLHttpRequest()
            request.open("POST", window.location.href, true)
            request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
            request.send("encoded_data=" + encodeURIComponent(JSON.stringify(api_parameters)))

            request.onload = function(response) {
              if (response.target.status == 200) {
                response = JSON.parse(response.target.response)

                if (typeof response.redirect == "string") {
                  window.location.href = response.redirect
                } else {
                  callback(response)
                }
              } else {
                const message_element = document.querySelector(".message[name=\"global\"]")
                message_element.innerText = "There was an error processing the request."
                message_element.classList.remove("hidden")
              }
            }
          }
        }

        const on_document_ready = function(callback) {
          if (document.readyState != "complete") {
            setTimeout(function() {
              on_document_ready(callback)
            }, 10)
          } else {
            callback()
          }
        }

        const select_elements = function(selector, callback) {
          const response = []
          const node_list = document.querySelectorAll(selector)

          if (node_list.length) {
            for (let node_list_key in node_list) {
              if (node_list.hasOwnProperty(node_list_key)) {
                response.push([node_list_key, node_list[node_list_key]])
              }
            }
          }

          if (typeof callback == "function") {
            for (let selected_element_key in response) {
              callback(selected_element_key, response[selected_element_key][1])
            }
          }

          return response
        }

        const api_parameters = {}

        on_document_ready(function() {
          const spin = function(selected_element, increment) {
            selected_element.innerText = (parseInt(selected_element.innerText) + increment) & 255
          }

          select_elements(".randomness-animation .delay-1", function(selected_element_key, selected_element) {
            setInterval(function(selected_element) {
              spin(selected_element, 11)
            }, 40, selected_element)
          })
          select_elements(".randomness-animation .delay-2", function(selected_element_key, selected_element) {
            setInterval(function(selected_element) {
              spin(selected_element, 71)
            }, 70, selected_element)
          })
          select_elements(".randomness-animation .delay-3", function(selected_element_key, selected_element) {
            setInterval(function(selected_element) {
              spin(selected_element, 101)
            }, 110, selected_element)
          })

          const redirect_element = document.querySelector(".redirect")

          if (redirect_element != null) {
            const redirect = redirect_element.innerText

            if (redirect != null) {
              window.location.href = redirect
            }
          }

          let session_id = ""

          if (navigator.cookieEnabled) {
            let i = document.cookie.indexOf("session_id=") + 11

            if (i == 10) {
              document.cookie = "session_id=" + Date.now() + "-" + Math.random() + "ghostproxies_; domain=ghostproxies.com; max-age=11111111; path=/; samesite=strict; secure"
              i = document.cookie.indexOf("session_id=") + 11
            }

            while (document.cookie[i] != "_") {
              session_id += document.cookie[i]
              i++
            }
          }

          api.set_parameters({
            session_id: session_id
          })

          if (document.querySelector(".form") != null) {
            const process_form = function() {
              const button_element = document.querySelector(".form button")
              const input = {}
              let parent_element

              select_elements(".form input, .form textarea", function(selected_element_key, selected_element) {
                parent_element = selected_element.parentElement

                while (
                  parent_element.classList.contains("form") == false &&
                  parent_element.classList.contains("hidden") == false
                ) {
                  parent_element = parent_element.parentElement
                }

                if (parent_element.classList.contains("form") == true) {
                  input[selected_element.getAttribute("name")] = selected_element.value
                }
              })
              select_elements(".form .checkbox", function(selected_element_key, selected_element) {
                input[selected_element.getAttribute("name")] = selected_element.classList.contains("active")
              })
              api.set_parameters({
                input: input
              })
              select_elements(".form .message", function(selected_element_key, selected_element) {
                selected_element.classList.add("hidden")
              })
              api.send(function(response) {
                for (const message_key in response.messages) {
                  const message_element = document.querySelector(".message[name=\"" + message_key + "\"]")
                  message_element.innerHTML = response.messages[message_key]
                  message_element.classList.remove("hidden")
                }
              })
            }

            select_elements(".form input", function(selected_element_key, selected_element) {
              selected_element.addEventListener("keydown", function() {
                if (event.key == "Enter") {
                  process_form()
                }
              })
            })
            select_elements(".form button", function(selected_element_key, selected_element) {
              selected_element.addEventListener("click", function() {
                process_form()
              })
            })
            select_elements(".form .checkbox", function(selected_element_key, selected_element) {
              selected_element.addEventListener("click", function() {
                selected_element.classList.toggle("active")
              })
            })
          }

          select_elements(".button-container input", function(selected_element_key, selected_element) {
            selected_element.addEventListener("keydown", function() {
              if (event.key == "Enter") {
                window.location = "https://ghostproxies.com/" + selected_element.value
              }
            })
          })
          select_elements(".button-container .button", function(selected_element_key, selected_element) {
            selected_element.addEventListener("click", function() {
              window.location = "https://ghostproxies.com/" + document.querySelector("#" + selected_element.getAttribute("name")).value
            })
          })
          select_elements(".seconds-ago", function(selected_element_key, selected_element) {
            setInterval(function(selected_element) {
              const increment = parseInt(selected_element.querySelector(".increment").innerText) + 1

              selected_element.querySelector(".increment").innerText = increment

              if (increment != 1) {
                selected_element.querySelector(".plural").innerText = "s"
              } else {
                selected_element.querySelector(".plural").innerText = ""
              }
            }, 1000, selected_element)
          })

          return
        })
      </script>
