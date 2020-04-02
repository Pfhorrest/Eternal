fogtimer = 420
pitch = .5
red = .125
green = 0
blue = .125
depth = 42
darken = false
	
Triggers = {}

function levelfog()
	-- Decrease fog counter
	fogtimer = fogtimer - 1

	-- Fluctuate fog brightness a bit
	if darken then
		if blue > .125 then
			red = red - 0.0005
			blue = blue - 0.0005
			depth = depth - 0.05
		else
			darken = false
		end
	elseif blue < .25 then
		red = red + 0.0005
		blue = blue + 0.0005
		depth = depth + 0.05
	else
		darken = true
	end

	if fogtimer > 30 then
		-- Normal fog values
		Level.fog.color.r = red
		Level.fog.color.g = green
		Level.fog.color.b = blue
		Level.fog.depth = depth
	else
		-- Fog flicker effect to simulate lightning
		mult = 1 + (fogtimer * (300 + Game.random(300)) / 6300)
		Level.fog.color.r = blue * mult
		Level.fog.color.g = ((blue * mult) - blue) * .7
		Level.fog.color.b = blue * mult
		Level.fog.depth = depth
		if fogtimer == 30 then
			-- Tag 7 activates/deactivates level lights 21-40 for lightning effect
			Tags[7].active = true
		end
		if fogtimer == 7 then
			-- Play thunder sound
			pitch = (200 + Game.random(200)) / 300
			for p in Players() do
				p:play_sound("surface explosion", pitch)
			end
		end
	end

	if fogtimer == 0 then
		-- Set next lightning interval (random from 7 to 21 seconds)
		fogtimer = 210 + Game.random(420)
		-- Deactivate lightning effect
		Tags[7].active = false
	end
end

function Triggers.init(restoring)
	Game.proper_item_accounting = true
	controllers = 0
	enforcers = 0
	done = false
	for m in Monsters() do
		if m.type == "mother of all cyborgs" then
			controllers = controllers + 1
		end
	end
	if restoring then
		Game.restore_saved()
	end
end

function Triggers.idle()
	if done == 2 then
		if Game.ticks % (Game.global_random(7)+1) == 0 then
			for m in Monsters() do
				if m.valid and not m.player then
					if m.visible and m.active then
						if m.type.class == "drone" then
							if m.index % (Game.global_random(7)+1) == 0 then
								m:damage(1,"claws")
							elseif m.index % (Game.random(7)+1) == 0 then
								m.active = false
							end
						end
					end
				end
			end
		end
  end
  levelfog()
end

function Triggers.monster_killed(monster, aggressor_player, projectile)
	if monster.type == "mother of all cyborgs" then
		controllers = controllers - 1
		if controllers <= 0 then
			for p in Polygons() do
				if p.type == "must be explored" then
					p.visible_on_automap = true
					p.type = 2
				end
			end
			done = true
			for m in Monsters() do
				if m.valid and not m.player then
					if m.type.class == "enforcer" then
						enforcers = enforcers + 1
					end
				end
			end
		end
	elseif monster.type.class == "enforcer" then
		if done == true then
			enforcers = enforcers - 1
			if enforcers <= 0 then
				for t in MonsterTypes() do
					if t.class == "cyborg" or t.class == "compiler" then
						t.enemies["player"] = false
						t.enemies["fighter"] = true
						t.enemies["trooper"] = true
						t.enemies["hunter"] = true
					elseif t.class == "drone" then
						t.enemies["player"] = false
						t.weaknesses["explosion"] = true
						t.weaknesses["projectile"] = true
						t.weaknesses["alien weapon"] = true
						t.weaknesses["hunter"] = true
						t.weaknesses["fists"] = true
						t.weaknesses["goo"] = true
						t.weaknesses["drone"] = true
						t.weaknesses["shotgun"] = true
					end
				end
				for m in Monsters() do
					if m.type.class == "bob" then
						m:damage(42,"claws")
					elseif m.valid and not m.player then
						if m.visible and m.active then
							if m.life > 0 then
								if m.type.class == "cyborg" or m.type.class == "drone" or m.type.class == "compiler" then
									m.life = m.life + 50
									m.active = false
								end
							end
						end
					end
				end
				done = 2
			end
		end
	end
end

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
