<?php
namespace kufi\BattleshipBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * abstract base class for games
 * 
 * @author kufi
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="game_type", type="string")
 * @ORM\DiscriminatorMap({"sp" = "SingleplayerGame", "mp" = "MultiplayerGame"})
 */
abstract class Game
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\generatedValue
	 */
	protected $id;
	
	/**
	 *
	 * @ORM\OneToMany(targetEntity="Field1", mappedBy="game", cascade={"all"})
	 */
	protected $user1Fields;
	
	/**
	 * 
	 * @ORM\OneToMany(targetEntity="Field2", mappedBy="game", cascade={"all"})
	 */
	protected $user2Fields;
	
	/**
	 * 
	 * @ORM\OneToMany(targetEntity="Ship1", mappedBy="game", cascade={"all"})
	 */
	protected $user1Ships;
	
	/**
	 * 
	 * @ORM\OneToMany(targetEntity="Ship2", mappedBy="game", cascade={"all"})
	 */
	protected $user2Ships;
	
    public function __construct()
    {
        $this->user1Fields = new \Doctrine\Common\Collections\ArrayCollection();
    	$this->user2Fields = new \Doctrine\Common\Collections\ArrayCollection();
    	$this->user1Ships = new \Doctrine\Common\Collections\ArrayCollection();
    	$this->user2Ships = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add userFields
     *
     * @param kufi\BattleshipBundle\Entity\Field $userFields
     */
    public function addUser1Field(\kufi\BattleshipBundle\Entity\Field1 $userField)
    {
    	$userField->setGame($this);
        $this->user1Fields->add($userField);
    }
    
    /**
     * Add aiFields
     *
     * @param kufi\BattleshipBundle\Entity\Field $userFields
     */
    public function addUser2Field(\kufi\BattleshipBundle\Entity\Field2 $userField)
    {
    	$userField->setGame($this);
    	$this->user2Fields->add($userField);
    }
    
    public function addUser1Ship(\kufi\BattleshipBundle\Entity\Ship1 $ship)
    {
    	if(!$this->addShip($ship, $this->user1Fields))
    	{
    		return false;
    	}
    	
    	$ship->setGame($this);
    	$this->user1Ships->add($ship);
    	
    	return true;
    }
    
    public function addUser2Ship(\kufi\BattleshipBundle\Entity\Ship2 $ship)
    {
    	if(!$this->addShip($ship, $this->user2Fields))
    	{
    		return false;
    	}
    	 
    	$ship->setGame($this);
    	$this->user2Ships->add($ship);
    	
    	return true;
    }
    
    /**
     * internal function to check if a ship can be set a the given position or not
     * 
     * @param unknown_type $ship
     * @param unknown_type $shipColl
     */
    private function addShip(\kufi\BattleshipBundle\Entity\Ship $ship, $shipColl)
    {
    	$fieldsToSet = array();
    	
    	//loop over all fields and set the set has ship to true
    	//return false if the field already has a ship
    	for($i=0;$i<$ship->getLength();$i++)
    	{
    		$coll = $shipColl->filter(function($field) use ($i, $ship)
    		{
    			if($ship->getOrientation() == 1)
    			{
    				return $field->getX() == $ship->getX() && $field->getY() == ($ship->getY() + $i) && !$field->getHasShip();
    			}
    			else
    			{
    				return $field->getX() == ($ship->getX() + $i) && $field->getY() == $ship->getY() && !$field->getHasShip();
    			}
    		});
    		 
    		if($coll->isEmpty()) {
    			return false;
    		} else {
    			$fieldsToSet[] = $coll->first();
    		}
    	}
    	
    	foreach($fieldsToSet as $field)
    	{
    		$field->setHasShip(true);
    	}
    	
    	return true;
    }
    
    /**
     * Get userFields
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUser1Fields()
    {
        return $this->user1Fields;
    }

    /**
     * Get user 2 fields
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUser2Fields()
    {
        return $this->user2Fields;
    }
    
    /**
     * Get user 1 ships
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getUser1Ships()
    {
    	return $this->user1Ships;
    }
    
    /**
     * Get user 2 ships
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getUser2Ships()
    {
    	return $this->user2Ships;
    }
    
    /**
     * checks if user1 already has set a ship with the given length
     * @param unknown_type $length
     */
    public function hasShip1WithLength($length)
    {
    	return $this->user1Ships->filter(function($s) use($length) {
    		return $s->getLength() == $length;
    	})->count() > 0;
    }
    
    /**
     * sets the fields of user1 automatically (for ai purposes)
     */
    public function setUser2FieldsAutomatically()
    {
    	//just loop randomly until all ships are set, starting with the biggest ship
    	for($i=5;$i>=1;$i--) {
    		do {
	    		$ship = new Ship2(mt_rand(0, 10), mt_rand(0, 10), $i, mt_rand(1, 2));
	    		$shipSet = $this->addUser2Ship($ship);
    		} while(!$shipSet);
    	}
    }
    
    /**
     * checks if the field is already marked as isHit or not
     * @param int $x
     * @param int $xy
     */
    public function checkAlreadyHitUser1($x, $y)
    {
    	return $this->checkAlreadyHit($x, $y, $this->user1Fields);
    }
    
    public function checkAlreadyHitUser2($x, $y)
    {
    	return $this->checkAlreadyHit($x, $y, $this->user2Fields);
    }
    
    private function checkAlreadyHit($x, $y, $fields)
    {
    	$ret = $fields->filter(function($field) use($x, $y) {
    		return $field->getX() == $x && $field->getY() == $y && $field->getIsHit();
    	});
    	 
    	return !$ret->isEmpty();
    }
    
    /**
     * 
     * shoots onto the field of player 2 and returns if hit or not
     * 
     * @param int $x
     * @param int $y
     */
    public function hitFieldUser1($x, $y)
    {
    	return $this->hitField($x, $y, $this->user1Fields);
    }
    
    /**
     *
     * shoots onto the field of player 2 and returns if hit or not
     *
     * @param int $x
     * @param int $y
     */
    public function hitFieldUser2($x, $y)
    {
    	return $this->hitField($x, $y, $this->user2Fields);
    }
    
    private function hitField($x, $y, $fields)
    {
    	$ret = $fields->filter(function($field) use($x, $y) {
    		return $field->getX() == $x && $field->getY() == $y;
    	});
    	 
    	//return true if no invalid field
    	if($ret->isEmpty())
    	{
    		return true;
    	}
    	$field = $ret->first();
    	
    	//return true if already hit
    	if($field->getIsHit())
    	{
    		return true;
    	}
    	$field->setIsHit(true);
    	
    	return $field->getHasShip();
    }
    
    /**
     * shoots automatically for the user (if in singleplayer for the ai)
     */
    public function user2ShootAutomatically()
    {
    	//TODO use difficulty level
    	
    	//just randomly shoot somewhere where we havent already shot (not really sophisticated)
    	//shoot until we hit an empty field
    	
    	$ret = $this->user2Fields->filter(function($field) {
    		return !$field->getIsHit();
    	});
    	
    	//return empty array if all fields have been shot
    	if($ret->count() == 0)
    	{
    		return array();
    	}
    	
    	//shoot onto fields
    	$rand = mt_rand(0, $ret->count() - 1);
    	$keys = $ret->getKeys();
    	
    	$fieldToShoot = $ret->get($keys[$rand]);
    	
    	if($this->hitFieldUser1($fieldToShoot->getX(), $fieldToShoot->getY()))
    	{
    		//if we hit something, call us again and add the field to the resulting array
    		$fields = $this->user2ShootAutomatically();
    		$fields[] = $fieldToShoot;
    		return $fields;
    	}
    	else
    	{
    		//if we hit nothing, just return a new array with the shot field
    		return array($fieldToShoot);
    	}
    }
}