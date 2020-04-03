CollectionsUsed = { 23 }

precipitation_type = "water lamp breaking"
precipitation_count = 640
precipitation_phase = 1
precipitation_gravity = 1/4
precipitation_wind = 0
fogtimer = 420
pitch = .5
red = .125
depth = 42
darken = false

function build_pool()
	Level._pool = {}
	
	local count = 0
	for i = 1, precipitation_count do
		 local x, y, z, p = uniform.xyz_in_triangle_list(Level._triangles)
		 e = Effects.new(x, y, z, p, precipitation_type)
		 if e then
			  count = count + 1
			  table.insert(Level._pool, e)
		 end
	end
	precipitation_count = count
end

function restore_pool()
	Level._pool = {}
	local count = 0
	for e in Effects() do
		if e.type == precipitation_type then
			count = count + 1
			table.insert(Level._pool, e)
		end
	end
	precipitation_count = count
end

function delete_pool()
	Level._pool = {}
	for e in Effects() do
		if e.type == precipitation_type then
			e:delete()
		end
	end
end

function precipitation_exists()
	for e in Effects() do
		if e.type == precipitation_type then
			return true
		end
	end

	return false
end

function precipitation_is_on()
	if not precipitation_checked then
		for a in Annotations() do
			if a.text == "ETERNAL_PRECIPITATION_ON" then
				a.polygon = nil
				a.text = ""
				precipitation_on = true
			end
		end
		precipitation_checked = true
	end

	return precipitation_on
end

function levelfog()
	-- Decrease fog counter
	fogtimer = fogtimer - 1

	-- Fluctuate fog brightness a bit
	if darken then
		if red > .125 then
			red = red - 0.0005
			depth = depth - 0.05
		else
			darken = false
		end
	elseif red < .25 then
		red = red + 0.0005
		depth = depth + 0.05
	else
		darken = true
	end

	if fogtimer > 30 then
		-- Normal fog values
		Level.fog.color.r = red
		Level.fog.color.g = red * .5
		Level.fog.color.b = 0
		Level.fog.depth = depth
	else
		-- Fog flicker effect to simulate lightning
		mult = 1 + (fogtimer * (300 + Game.random(300)) / 6300)
		Level.fog.color.r = red * mult
		Level.fog.color.g = ((red * mult * .85) - (red * .35))
		Level.fog.color.b = ((red * mult) - red) * .7
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

function initprecipitation()
	local polygon_list = {}
	for p in Polygons() do
		if p.ceiling.transfer_mode == "landscape" then
			table.insert(polygon_list, p)
		end
	end
	Level._triangles = uniform.build_triangle_list(polygon_list)
	if #polygon_list == 0 then
		precipitation_count = 0
	end

	if precipitation_is_on() and not precipitation_exists() then
		build_pool()
	elseif precipitation_is_on() then
		restore_pool()
	else
		delete_pool()
	end
end

function idleprecipitation()
	if not precipitation_checked then
		initprecipitation()
	end

	if precipitation_is_on() then
		local pool = Level._pool
		local position = pool[1].position
		local phase = precipitation_phase
		local gravity = phase * precipitation_gravity
		local wind = phase * precipitation_wind
		local phase_match = Game.ticks % phase
		for i = 1,precipitation_count do
			if i % phase == phase_match then
				local e = pool[i]
				position(e, e.x - wind, e.y - wind, e.z - gravity, e.polygon)
				if e.z < e.polygon.floor.height then
					local x, y, p = uniform.xy_in_triangle_list(Level._triangles)
					e:position(x, y, p.ceiling.height, p)
				elseif e.z > e.polygon.ceiling.height then
					local x, y, p = uniform.xy_in_triangle_list(Level._triangles)
					e:position(x, y, p.floor.height, p)
				end
				if e.polygon.media then
					if e.z < e.polygon.media.height then
						local x, y, p = uniform.xy_in_triangle_list(Level._triangles)
						e:position(x, y, p.ceiling.height, p)
					end
				end
			end
		end
	end
end

function Triggers.init(restoring)
	Game.proper_item_accounting = true
end

function Triggers.idle()
	idleprecipitation()
   	levelfog()
end