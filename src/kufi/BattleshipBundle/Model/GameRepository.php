<?php
namespace kufi\BattleshipBundle\Model;

use kufi\BattleshipBundle\kufiBattleshipBundle;
use Doctrine\ORM\EntityManager;

class GameRepository
{
	
	private $em;
	
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}
	
	public function addGame($game)
	{
		$this->em->persist($game);
		$this->em->flush();
	}
	
	public function updateGame($game)
	{
		$this->em->persist($game);
		$this->em->flush();
	}
	
	public function getGame($id)
	{
		return $this->em->getRepository("kufiBattleshipBundle:Game")->find($id);
	}
	
	public function getAllOpenMultiplayerGames()
	{
	    return $this->em->createQuery("SELECT g FROM kufi\BattleshipBundle\Entity\MultiplayerGame g WHERE g.playerOne IS NOT NULL AND g.playerTwo IS NULL")->getResult();
	}
}
