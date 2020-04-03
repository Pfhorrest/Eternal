Triggers = {}

function Triggers.got_item(type, player)

	if type == "pistol" then
		if player.items["pistol"] > 2 then
			player.items["pistol"] = 2
			player.items["pistol ammo"] = player.items["pistol ammo"] + 1
		end

	elseif type == "fusion pistol" then
		if player.items["fusion pistol"] > 1 then
			player.items["fusion pistol"] = 1
			player.items["pistol ammo"] = player.items["pistol ammo"] + 1
		end

	elseif type == "assault rifle" then
		if player.items["assault rifle"] > 1 then
			player.items["assault rifle"] = 1
			player.items["assault rifle ammo"] = player.items["assault rifle ammo"] + 1
		end

	elseif type == "missile launcher" then
		if player.items["missile launcher"] > 1 then
			player.items["missile launcher"] = 1
			player.items["missile launcher ammo"] = player.items["missile launcher ammo"] + 1
			player.items["assault rifle grenades"] = player.items["assault rifle grenades"] + 1
		end

	elseif type == "alien weapon" then
		if player.items["alien weapon"] > 1 then
			player.items["alien weapon"] = 1
			player.items["shotgun ammo"] = player.items["shotgun ammo"] + 1
		end

	elseif type == "flamethrower" then
		if player.items["flamethrower"] > 1 then
			player.items["flamethrower"] = 1
			player.items["flamethrower ammo"] = player.items["flamethrower ammo"] + 1
		end

	elseif type == "shotgun" then
		if player.items["shotgun"] > 2 then
			player.items["shotgun"] = 2
			player.items["smg ammo"] = player.items["smg ammo"] + 1
		end

	elseif type == "smg" then
		if player.items["smg"] > 1 then
			player.items["smg"] = 1
			player.items["smg ammo"] = player.items["smg ammo"] + 1
		end

	elseif type == "assault rifle ammo" then
		if player.items["assault rifle"] < 1 then
			player.items["assault rifle ammo"] = player.items["assault rifle ammo"] - 1
			player.items["assault rifle"] = 1
		end

	elseif type == "assault rifle grenades" then
		if player.items["missile launcher"] < 1 then
			player.items["assault rifle grenades"] = player.items["assault rifle grenades"] - 1
			player.items["missile launcher"] = 1
		end

	elseif type == "missile launcher ammo" then
		if player.items["missile launcher"] < 1 then
			player.items["missile launcher ammo"] = player.items["missile launcher ammo"] - 1
			player.items["missile launcher"] = 1
		else
			player.items["assault rifle grenades"] = player.items["assault rifle grenades"] + 1
		end

	elseif type == "shotgun ammo" then
		if player.items["alien weapon"] < 1 then
			player.items["shotgun ammo"] = player.items["shotgun ammo"] - 1
			player.items["alien weapon"] = 1
		end

	elseif type == "flamethrower ammo" then
		if player.items["flamethrower"] < 1 then
			player.items["flamethrower ammo"] = player.items["flamethrower ammo"] - 1
			player.items["flamethrower"] = 1
		end

	elseif type == "fusion pistol ammo" then
		player.items["fusion pistol ammo"] = 0
		player.items["pistol ammo"] = player.items["pistol ammo"] + 1
	
	elseif type == "alien weapon ammo" then
		player.items["alien weapon ammo"] = 0
		if player.items["flamethrower ammo"] > player.items["shotgun ammo"] then
			player.items["shotgun ammo"] = player.items["shotgun ammo"] + 1
		else
			player.items["flamethrower ammo"] = player.items["flamethrower ammo"] + 1
		end
	end
end

function Triggers.player_damaged(victim, aggressor_player, aggressor_monster, damage_type, damage_amount, projectile)
	if projectile and (victim.weapons.current.type == "smg" or victim.weapons.current.type == "shotgun") then
		if projectile.type == "shotgun bullet" or projectile.type == "smg bullet" then
			victim.life = victim.life + damage_amount
		end
	end
end

function Triggers.terminal_enter(terminal, player)
	if terminal == 3 and not bobdead then
		for m in Monsters() do
			if m.valid and m.type == "green bob" then
				m.active = true
				m.life = Game.random(m.life)
			end
		end
	end
end

function Triggers.monster_killed(monster, aggressor_player, projectile)
	if monster.type == "green bob" and not bobdead then
		for p in Polygons() do
			if p.type == "must be explored" then
				p.visible_on_automap = true
				p.type = 2
			end
		end
		for p in Platforms() do
			if p.polygon.floor.z < -20 then
				p.active = true
			end
		end
		bobdead = true
	end
end

function Triggers.init(restoring)
	Game.proper_item_accounting = true
	if not restoring then
		for p in Players() do
			if p.life < 450 and not p.dead then
				p.life = 450
			end
		end
	end
end
