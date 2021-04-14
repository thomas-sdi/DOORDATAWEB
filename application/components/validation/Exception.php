<?
class Validation_Exception extends Zend_Exception {
	protected $_violatedRules;
	protected $_violatedFields;
	
	/**
	 * @param Validation_Rule|array $rule   Validation rule or array of {rule, violated}
	 * @param array                 $fields List of fields violated the rule or NULL    
	 */
	public function __construct($rule, $fields=null) {
	    $rule = is_array($rule) ? $rule : array(array('Rule' => $rule, 'Fields' => $fields));
	    foreach ($rule as $violation) {
	    	$ruleId = $violation['Rule']->getId();
	    	$this->_violatedRules[$ruleId]  = $violation['Rule'];
	    	$this->_violatedFields[$ruleId] = $violation['Fields']; 
	    }
		
		// pass correct message to parent so getMessage() works correctly 
		parent::__construct($this->getMessageJson());
	}
    
    /**
     * Returns list of problems of given severity occured in the Exception
     * @param  $severity     What kind of problems is requested - either errors or warnings, or all
     * @param  $translateMap Allows to translate violated fields according to the map: field=>translatedValue
     *                       Also automatically 'translates' rule object into message 
     * @return array[]       List of problems: ['Rule' => problemRule, 'Fields' => problemFields]
     */
    public function getProblems($severity=NULL, $translateMap=NULL) {
    	$problems = array();
        foreach ($this->_violatedRules as $ruleId => $rule) {
        	// check severity matching
            if ($severity !== NULL && $rule->getSeverity() != $severity)
                continue;
            
            // get violated fields for this rule and translate them if needed
            $violatedFields = $this->_violatedFields[$ruleId];
            if ($translateMap) {
                foreach ($violatedFields as $key => $field) {
                	$field = strtoupper($field);
                    if (array_key_exists($field, $translateMap))
                        $violatedFields[$key] = $translateMap[$field];
                }
            }        
            // add complete problem into the list
            $problems[$ruleId] = array(
                'Rule'   => $translateMap ? $rule->getMessage($translateMap) : $rule,
                'Fields' => $violatedFields);
        }
        return count($problems) ? $problems : NULL;
    }
    
    public function getErrors($translateMap) {
        return $this->getProblems(Validation_Rule::ERROR, $translateMap);
    }
    
    public function getWarnings($translateMap) {
        return $this->getProblems(Validation_Rule::WARN, $translateMap);
    }
    
    public function getMessageJson($translateMap = null) {
        $problems = array();
        foreach($this->getProblems($translateMap) as $problem) {
            foreach($problem['Fields'] as $field) {    
                $problems[strtolower($field)] = $problem['Rule']->getMessage(); 
            }
        }
        return Zend_Json::encode($problems);
    }
}