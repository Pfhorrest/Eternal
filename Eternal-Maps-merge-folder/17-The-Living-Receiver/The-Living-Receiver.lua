Triggers = {}

function oxygensounds(p)
	-- plays breathing and oxygen warning sounds when oxygen is deplenishing to mimic normal game behaviour
	if p.oxygen > 900 then
		if p.oxygen % 900 == 899 then
			p:play_sound("breathing", 1)
		end
	else
		if p.oxygen % 450 == 449 then
			p:play_sound("oxygen warning", 1) 
		end
	end
end

function Triggers.idle()
	for p in Players() do
		-- if p.infravision_duration <= 1 then
			-- p.infravision_duration = 2 -- we want players to have infravision throughout the course of this level, because it's a dream
		-- end
		if p.polygon.media then
			if p.oxygen <= 0 then -- we have to kill the player manually if they're in a vacuum and their oxygen is at or below 0
				p:damage(p.life + 1, "suffocation")
			end
			local oxydrain = 0 -- sets the amount of oxygen to drain from player this cycle; default to 0
			if p.polygon.media.type == "jjaro" then
				oxydrain = 4
			elseif p.polygon.media.type == "sewage" and Polygons[958].ceiling.height > -0.2 then
				oxydrain = 3
			elseif p.polygon.media.type == "sewage" and Polygons[957].ceiling.height > 0.05 and Polygons[565].ceiling.height > 1.7 then 
				oxydrain = 2
			elseif p.polygon.media.type == "sewage" and Polygons[957].ceiling.height > 0.05 and Polygons[636].ceiling.height > 1.7 then 
				oxydrain = 2
			elseif p.polygon.media.type == "water" and Polygons[565].ceiling.height > 1.7 then 
				oxydrain = 3
			elseif p.polygon.media.type == "water" and Polygons[636].ceiling.height > 1.7 then 
				oxydrain = 3
			elseif p.polygon.media.type == "water" and Polygons[958].ceiling.height > -0.2 and Polygons[957].ceiling.height > 0.05 then
				oxydrain = 2
			elseif p.polygon.media.type == "lava" and Polygons[565].ceiling.height > 1.7 and Polygons[657].ceiling.height > 0.6 then 
				oxydrain = 2
			elseif p.polygon.media.type == "lava" and Polygons[636].ceiling.height > 1.7 and Polygons[657].ceiling.height > 0.6 then 
				oxydrain = 2
			elseif p.polygon.media.type == "lava" and Polygons[958].ceiling.height > -0.2 and Polygons[957].ceiling.height > 0.05 and Polygons[657].ceiling.height > 0.6 then
				oxydrain = 2
			elseif p.polygon.media.type == "goo" and Polygons[958].ceiling.height > -0.2 and Polygons[957].ceiling.height > 0.05 and Polygons[657].ceiling.height > 0.6 and Polygons[671].ceiling.height > 1.6 then
				oxydrain = 1
			elseif p.polygon.media.type == "goo" and Polygons[565].ceiling.height > 1.7 and Polygons[657].ceiling.height > 0.6 and Polygons[671].ceiling.height > 1.6 then 
				oxydrain = 1
			elseif p.polygon.media.type == "goo" and Polygons[636].ceiling.height > 1.7 and Polygons[657].ceiling.height > 0.6 and Polygons[671].ceiling.height > 1.6 then 
				oxydrain = 1
			elseif p.polygon.media.type == "goo" and Polygons[565].ceiling.height > 1.7 and Polygons[515].ceiling.height > 1 then 
				oxydrain = 1
			elseif p.polygon.media.type == "goo" and Polygons[636].ceiling.height > 1.7 and Polygons[515].ceiling.height > 1 then 
				oxydrain = 1
			elseif p.polygon.media.type == "goo" and Polygons[958].ceiling.height > -0.2 and Polygons[957].ceiling.height > 0.05 and Polygons[515].ceiling.height > 1 then
				oxydrain = 1
			end
			if oxydrain > 0 then
				local n = 0
				for n = 1,oxydrain do -- cycle to check for breathing sound threshold for each bit of oxygen we drain
					p.oxygen = p.oxygen - 1
					if n > 1 then -- not actually draining oxygen yet if n = 1, since Eternal restores oxygen outside media
						oxygensounds(p)
					end
				end
				if oxydrain > 1 and (Game.difficulty == "major damage" or Game.difficulty == "total carnage") and (p.action_flags.left_trigger or p.action_flags.right_trigger) then
					p.oxygen = p.oxygen - 1 -- game drains oxygen faster on TC and MD if player is firing weapon
					oxygensounds(p)
				end
				-- should also drain faster on TC if player is holding run key, but no Lua check for that exists. rip
			end
		end
	end
	if Game.ticks % 7 == 0 then
		for m in Monsters() do
			if m.polygon.media and m.type.impact_effect ~= "civilian fusion blood splash" and m.action ~= "dying hard" and m.action ~= "dying soft" and m.action ~= "dying flaming" and m.action ~= "being hit" then
				if m.type.class == "yeti" or m.type.class == "bob" or m.type.class == "fighter" or m.type.class == "enforcer" then
					m._canbreathe = true
					if m.polygon.media.type == "jjaro" then
						m._canbreathe = false
					elseif Polygons[565].ceiling.height > 1.7 or Polygons[636].ceiling.height > 1.7 then
						if m.polygon.media.type == "water" then
							m._canbreathe = false
						elseif Polygons[657].ceiling.height > 0.6 then
							if m.polygon.media.type == "lava" then
								m._canbreathe = false
							elseif m.polygon.media.type == "goo" and Polygons[671].ceiling.height > 1.6 then
									m._canbreathe = false
							end
						elseif Polygons[515].ceiling.height > 1 and m.polygon.media.type == "goo" then
							m._canbreathe = false
						end
					elseif Polygons[958].ceiling.height > -0.2 then
						if m.polygon.media.type == "sewage" then
							m._canbreathe = false
						elseif Polygons[957].ceiling.height > 0.05 then
							if m.polygon.media.type == "water" then
								m._canbreathe = false
							elseif Polygons[657].ceiling.height > 0.6 then
								if m.polygon.media.type == "lava" then
									m._canbreathe = false
								elseif m.polygon.media.type == "goo" and Polygons[671].ceiling.height > 1.6 then
									m._canbreathe = false
								end
							elseif Polygons[515].ceiling.height > 1 and m.polygon.media.type == "goo" then
								m._canbreathe = false
							end
						end
					end
					if m._canbreathe == false then
						m._canbreathe = true
						if not m.active then
							m:move_by_path(337)
						elseif m.type.class == "yeti" and Game.ticks % 2 == 0 then
							m:damage(4, "crushing")
						elseif Game.ticks % 4 == 0 then
							m:damage(1, "crushing")
						elseif m.action ~= "attacking close" and m.action ~= "attacking far" and m.life > 0 then
							m:move_by_path(337)
						end
					end
				end
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

function Triggers.init(restoring)
	Game.proper_item_accounting = true
end
