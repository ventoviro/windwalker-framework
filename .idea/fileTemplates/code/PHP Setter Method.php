/**
 * @param   ${TYPE_HINT}  $${PARAM_NAME}
 *
#if (${STATIC} == "static")
 * @return  void
#else
 * @return  static  Return self to support chaining.
#end
 */
public ${STATIC} function set${NAME}(#if (${SCALAR_TYPE_HINT})${SCALAR_TYPE_HINT} #else#end$${PARAM_NAME})#if (${STATIC} == "static"):void#else:static#end
{
#if (${STATIC} == "static")
    static::$${FIELD_NAME} = $${PARAM_NAME};
#else
    $this->${FIELD_NAME} = $${PARAM_NAME};
    
    return $this;
#end
}
