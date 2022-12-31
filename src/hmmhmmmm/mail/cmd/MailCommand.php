<?php

declare(strict_types=1);

namespace hmmhmmmm\mail\cmd;

use hmmhmmmm\mail\Mail;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use pocketmine\plugin\PluginOwned;

class MailCommand extends Command implements PluginOwned
{
   private $plugin;
   public function __construct(Mail $plugin){
      parent::__construct("mail");
      $this->plugin = $plugin;
   }
   public function getPlugin(): Plugin{
      return $this->plugin;
   }
   public function sendConsoleError(CommandSender $sender): void{
      $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.consoleError"));
   }
   public function sendPermissionError(CommandSender $sender): void{
      $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.permissionError"));
   }
   public function getPrefix(): string{
      return $this->getPlugin()->getPrefix();
   }
   public function sendHelp(CommandSender $sender): void{
      $sender->sendMessage($this->getPrefix()." : §fCommand");
      if($sender->hasPermission("mail.command.info")){
         $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("mail.command.info.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("mail.command.info.description"));
      }
      if($sender->hasPermission("mail.command.write")){
         $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("mail.command.write.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("mail.command.write.description"));
      }
      if($sender->hasPermission("mail.command.read")){
         $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("mail.command.read.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("mail.command.read.description"));
      }
      if($sender->hasPermission("mail.command.readall")){
         $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("mail.command.readall.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("mail.command.readall.description"));
      }
      if($sender->hasPermission("mail.command.clear")){
         $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("mail.command.clear.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("mail.command.clear.description"));
      }
      if($sender->hasPermission("mail.command.clearall")){
         $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("mail.command.clearall.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("mail.command.clearall.description"));
      }
      if($sender->hasPermission("mail.command.see")){
         $sender->sendMessage("§a".$this->getPlugin()->getLanguage()->getTranslate("mail.command.see.usage")." : ".$this->getPlugin()->getLanguage()->getTranslate("mail.command.see.description"));
      }
      if($sender->hasPermission("mail.command.read") && $sender->hasPermission("mail.command.readall")){
         $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.sendHelp.countmail", [$this->getPlugin()->getCountMail($sender->getName())]));
         $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.sendHelp.listplayer"));
         if($this->getPlugin()->getMailSenderCount($sender->getName()) == 0){
            $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.sendHelp.notfoundmessage"));
         }else{
            foreach($this->getPlugin()->getMailSender($sender->getName()) as $playerName){
               $sender->sendMessage($this->getPlugin()->listMail($sender->getName(), $playerName));
            }
         }
      }
   }
   public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
      if(!$sender instanceof Player){
         $this->sendConsoleError($sender);
         return true;
      }
      if (empty($args[0])) {
         $this->getPlugin()->getForm()->MailMenu($sender);
         $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.sendHelp.empty"));
         return false;
      }
      if ($args[0] == "help") {
         $this->sendHelp($sender);
      }
      if ($args[0] == "write") {
            if(!$sender->hasPermission("mail.command.write")){
               $this->sendPermissionError($sender);
               return false;
            }
            if(count($args[1])){
               $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.write.error1", [$this->getPlugin()->getLanguage()->getTranslate("mail.command.write.usage")]));
               return true;
            }
            $name = array_shift($args);
            $playerData = $this->getPlugin()->getPlayerData($name);
            if(!$playerData->isData()){
               $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("playerdata.notfoundname"));
               return true;
            }
            $this->getPlugin()->array[$sender->getName()] = $playerData->getName();
            $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("mail.command.write.complete"));
      }
      if ($args[0] == "info") {
            if(!$sender->hasPermission("mail.command.info")){
               $this->sendPermissionError($sender);
               return false;
            }
      }
      if ($args[0] == "read") {
            if(!$sender->hasPermission("mail.command.read")){
               $this->sendPermissionError($sender);
               return false;
            }
            if(count($args) < 1){
               $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.read.error1", [$this->getPlugin()->getLanguage()->getTranslate("mail.command.read.usage")]));
               return false;
            }
            if($this->getPlugin()->getMailSenderCount($sender->getName()) == 0){
               $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.read.error2"));
               return false;
            }
            $playerName = array_shift($args);
            $playerName = strtolower($playerName);
            if(!$this->getPlugin()->isMailSender($sender->getName(), $playerName)){
               $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.read.error3"));
               return false;
            }
            $count = $this->getPlugin()->getCountMail($sender->getName()) - $this->getPlugin()->getCountMailSender($sender->getName(), $playerName);
            $this->getPlugin()->setCountMail($sender->getName(), $count);
            $this->getPlugin()->setCountMailSender($sender->getName(), $playerName, 0);
            foreach($this->getPlugin()->getMailSenderWrite($sender->getName(), $playerName) as $msgCount2){
               $this->getPlugin()->setMailRead($sender->getName(), $playerName, $msgCount2, true);
            }
            foreach($this->getPlugin()->getMailSenderWrite($sender->getName(), $playerName) as $msgCount2){
               $sender->sendMessage($this->getPlugin()->readMail($sender->getName(), $playerName, $msgCount2));
            }
            $player = $this->getPlugin()->getServer()->getPlayer($playerName);
            if($player instanceof Player){
               $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("mail.command.read.complete", [$sender->getName()]));
            }
      }
      if ($args[0] == "read-all") {
            if(!$sender->hasPermission("mail.command.readall")){
               $this->sendPermissionError($sender);
               return false;
            }
            if($this->getPlugin()->getMailSenderCount($sender->getName()) == 0){
               $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.readall.error1"));
               return false;
            }
            foreach($this->getPlugin()->getMailSender($sender->getName()) as $playerName){
               if(!$this->getPlugin()->isMailSender($sender->getName(), $playerName)){
                  $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.readall.error2"));
                  return false;
               }
               $count = $this->getPlugin()->getCountMail($sender->getName()) - $this->getPlugin()->getCountMailSender($sender->getName(), $playerName);
               $this->getPlugin()->setCountMail($sender->getName(), $count);
               $this->getPlugin()->setCountMailSender($sender->getName(), $playerName, 0);
               foreach($this->getPlugin()->getMailSenderWrite($sender->getName(), $playerName) as $msgCount2){
                  $this->getPlugin()->setMailRead($sender->getName(), $playerName, $msgCount2, true);
               }
               foreach($this->getPlugin()->getMailSenderWrite($sender->getName(), $playerName) as $msgCount2){
                  $sender->sendMessage($this->getPlugin()->readMail($sender->getName(), $playerName, $msgCount2));
               }
               $player = $this->getPlugin()->getServer()->getPlayer($playerName);
               if($player instanceof Player){
               $player->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("mail.command.readall.complete", [$sender->getName()]));
            }
         }        
      }
      if ($args[0] == "clear") {
            if(!$sender->hasPermission("mail.command.clear")){
               $this->sendPermissionError($sender);
               return false;
            }
            if(count($args[2])){
               $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.clear.error1", [$this->getPlugin()->getLanguage()->getTranslate("mail.command.clear.usage")]));
               return false;
            }
            $name = array_shift($args);                            
            $msgCount = array_shift($args);
            if(!is_numeric($args[1])){
               $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("mail.command.clear.error2"));
               return false;
            }
            $this->getPlugin()->delMailSender($sender, strtolower($name), $msgCount);
      }
      if ($args[0] == "clear-all") {
            if(!$sender->hasPermission("mail.command.clearall")){
               $this->sendPermissionError($sender);
               return false;
            }
            $this->getPlugin()->resetMail($sender->getName());
            $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("mail.command.clearall.complete"));
      }
      if ($args[0] == "see") {
            if(!$sender->hasPermission("mail.command.see")){
               $this->sendPermissionError($sender);
               return false;
            }
            if(count($args[1])){
               $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.see.error1", [$this->getPlugin()->getLanguage()->getTranslate("mail.command.see.usage")]));
               return false;
            }
            $name = array_shift($args);
            $playerData = $this->getPlugin()->getPlayerData($name);
            if(!$playerData->isData()){
               $sender->sendMessage($this->getPrefix()." ".$this->getPlugin()->getLanguage()->getTranslate("playerdata.notfoundname"));
               return false;
            }
            if(!$this->getPlugin()->isMailSender($playerData->getName(), strtolower($sender->getName()))){
               $sender->sendMessage($this->getPlugin()->getLanguage()->getTranslate("mail.command.see.error2"));
               return false;
            }
            foreach($this->getPlugin()->getMailSenderWrite($playerData->getName(), strtolower($sender->getName())) as $msgCount2){
               $sender->sendMessage($this->getPlugin()->readMail($playerData->getName(), strtolower($sender->getName()), $msgCount2));
            }
      }
   }
    public function getOwningPlugin() : Plugin {
        return Mail::$instance;
    }
}